<?php
// Configure error handling: disable display to keep API JSON clean
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Buffer and discard any accidental output from includes so we always return clean JSON
ob_start();
require_once __DIR__ . '/../model/report.php';
require_once __DIR__ . '/../model/comment.php';
require_once __DIR__ . '/../model/post.php';
ob_end_clean();

// Ensure reports table exists; if creation fails return JSON error
try {
    ReportCRUD::createTable();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'DB init error: ' . $e->getMessage()]);
    exit;
}

class ReportController {
    /**
     * Create a new report
     */
    public function createReport($content_type, $content_id, $reported_by, $reason) {
        try {
            $id = ReportCRUD::create($content_type, $content_id, $reported_by, $reason);
            return ['success' => true, 'report_id' => $id];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all reports with content details
     */
    public function getAllReports() {
        try {
            return ReportCRUD::getAll();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Dismiss a report
     */
    public function dismissReport($id) {
        try {
            $success = ReportCRUD::dismiss($id);
            return ['success' => $success];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a report
     */
    public function deleteReport($id) {
        try {
            $success = ReportCRUD::delete($id);
            return ['success' => $success];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete content and all associated reports
     */
    public function deleteContentWithReports($content_type, $content_id) {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            // Delete the content first
            if ($content_type === 'comment') {
                CommentCRUD::deleteComment($content_id);
            } else if ($content_type === 'post') {
                // Delete post directly using SQL (same as communityController)
                $stmt1 = $db->prepare("DELETE FROM comments WHERE `comment id` = :post_id");
                $stmt1->execute([':post_id' => $content_id]);
                $stmt2 = $db->prepare("DELETE FROM post WHERE ID = :post_id");
                $stmt2->execute([':post_id' => $content_id]);
            }

            // Delete all reports for this content
            ReportCRUD::deleteByContent($content_type, $content_id);

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}

// API endpoint logic
header('Content-Type: application/json; charset=utf-8');

$controller = new ReportController();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new report
    if (isset($_POST['content_type']) && isset($_POST['content_id']) && isset($_POST['reason']) && !isset($_POST['report_id'])) {
        $content_type = $_POST['content_type'];
        $content_id = (int)$_POST['content_id'];
        $reported_by = isset($_POST['reported_by']) && $_POST['reported_by'] ? $_POST['reported_by'] : 'Anonyme';
        $reason = $_POST['reason'];

        // Validate content_type
        if ($content_type !== 'post' && $content_type !== 'comment') {
            echo json_encode(['success' => false, 'error' => 'Invalid content_type']);
            exit;
        }

        try {
            $result = $controller->createReport($content_type, $content_id, $reported_by, $reason);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
        exit;
    }

    // Dismiss report
    if (isset($_POST['report_id']) && isset($_POST['action']) && $_POST['action'] === 'dismiss') {
        try {
            $result = $controller->dismissReport((int)$_POST['report_id']);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
        exit;
    }

    // Delete content with reports
    if (isset($_POST['report_id']) && isset($_POST['action']) && $_POST['action'] === 'delete_content') {
        // First, get the report to know what content to delete
        try {
            $reports = $controller->getAllReports();
            $report = null;
            foreach ($reports as $r) {
                if ($r['id'] == $_POST['report_id']) {
                    $report = $r;
                    break;
                }
            }

            if (!$report) {
                echo json_encode(['success' => false, 'error' => 'Report not found']);
                exit;
            }

            $result = $controller->deleteContentWithReports($report['content_type'], $report['content_id']);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid POST request']);
    exit;
}

// Handle GET requests - Get all reports
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $reports = $controller->getAllReports();
        echo json_encode($reports);
    } catch (Exception $e) {
        error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
        echo json_encode([]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);
