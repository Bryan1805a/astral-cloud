<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\AdminEmail;

class InboxController extends Controller {
    public function index() {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }

        $userId = $_SESSION['user_id'];

        // Fetch admin emails
        $adminEmails = AdminEmail::getInboxForUser($userId);

        // Fetch system notifications
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, type, title, message, link, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Merge both into a single unified list
        $messages = [];

        foreach ($adminEmails as $email) {
            $messages[] = [
                'type'       => 'email',
                'id'         => $email['id'],
                'title'      => $email['subject'],
                'body'       => $email['body'],
                'from'       => $email['sender_name'],
                'link'       => null,
                'is_read'    => (bool) $email['is_read'],
                'created_at' => $email['sent_at'],
            ];
        }

        foreach ($notifications as $notif) {
            $messages[] = [
                'type'       => 'notification',
                'id'         => $notif['id'],
                'title'      => $notif['title'],
                'body'       => $notif['message'],
                'from'       => 'System',
                'link'       => $notif['link'],
                'is_read'    => (bool) $notif['is_read'],
                'created_at' => $notif['created_at'],
            ];
        }

        // Sort unified list by date descending
        usort($messages, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        view('inbox/index', [
            'messages' => $messages,
            'title'    => 'Inbox | Astral Cloud',
        ]);
    }

    public function markRead() {
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["user_id"])) {
            verifyCsrfToken();
            $id   = (int) $_POST["id"];
            $type = $_POST["type"] ?? 'email';

            if ($type === 'email') {
                AdminEmail::markAsRead($id, $_SESSION["user_id"]);
            } else {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $_SESSION["user_id"]]);
            }

            header("Location: /inbox");
            exit;
        }
    }
}
