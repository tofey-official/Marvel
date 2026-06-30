<?php
// Prevent multiple inclusions
if (!defined('GITHUB_SYNC_INCLUDED')) {
    define('GITHUB_SYNC_INCLUDED', true);

    require_once __DIR__ . '/github_config.php';

    // Register a shutdown function to automatically push changes at the end of the page execution
    register_shutdown_function(function() {
        // Only trigger sync if we are in admin session
        if (!isset($_SESSION['id'])) {
            return;
        }

        // Define repository root (the parent directory of includes/)
        $repo_root = dirname(__DIR__);

        // 1. Check git status to see if anything changed. If nothing changed, we do nothing (very fast).
        $cmd_status = "cd " . escapeshellarg($repo_root) . " && git status --porcelain 2>&1";
        $status_output = [];
        $status_retval = 0;
        @exec($cmd_status, $status_output, $status_retval);

        $has_changes = false;
        $changed_files = [];
        foreach ($status_output as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Ignore temporary SQLite journal files (*-journal, *-wal, *-shm)
            if (preg_match('/-journal|-wal|-shm$/', $line)) {
                continue;
            }
            
            // Ignore git sync log itself
            if (strpos($line, 'git_sync.log') !== false) {
                continue;
            }

            $has_changes = true;
            $changed_files[] = $line;
        }

        if ($has_changes) {
            // If fastcgi_finish_request exists, call it to send the response to the user immediately
            if (function_exists('fastcgi_finish_request')) {
                @session_write_close();
                @fastcgi_finish_request();
            }

            // Ensure the remote origin URL is correctly configured with the PAT token
            $token = GITHUB_TOKEN;
            $username = GITHUB_USERNAME;
            $repo = GITHUB_REPO;
            $branch = GITHUB_BRANCH;

            // Formulate remote URL with token authentication
            $remote_url = "https://{$username}:{$token}@github.com/{$username}/{$repo}.git";

            // Configure remote URL dynamically
            $cmd_set_remote = "cd " . escapeshellarg($repo_root) . " && git remote remove origin 2>/dev/null; git remote add origin " . escapeshellarg($remote_url) . " 2>&1";
            @exec($cmd_set_remote);

            // Determine commit message based on current page
            $script_name = basename($_SERVER['SCRIPT_NAME']);
            $commit_msg = "Panel update via " . $script_name . " at " . date('Y-m-d H:i:s');

            // Log details
            $log_file = $repo_root . "/api/git_sync.log";
            $log_content = "=== Git Sync Triggered ===\n";
            $log_content .= "Time: " . date('Y-m-d H:i:s') . "\n";
            $log_content .= "Changes detected:\n" . implode("\n", $changed_files) . "\n";

            // Execute git commands
            $commands = [
                "git add -A",
                "git commit -m " . escapeshellarg($commit_msg),
                "git push -u origin " . escapeshellarg($branch) . " 2>&1"
            ];

            foreach ($commands as $cmd) {
                $full_cmd = "cd " . escapeshellarg($repo_root) . " && " . $cmd;
                $cmd_out = [];
                $cmd_ret = 0;
                @exec($full_cmd, $cmd_out, $cmd_ret);
                $log_content .= "CMD: $cmd\nRET: $cmd_ret\nOUT:\n" . implode("\n", $cmd_out) . "\n";
            }
            $log_content .= "========================\n\n";

            @file_put_contents($log_file, $log_content, FILE_APPEND);
        }
    });
}
