<?php
class Voucher {
    public static function create(Database $db, $code, $event_name, $template, $valid_from = null, $valid_to = null, $user_id = null) {
        $pdo = $db->pdo();
        $stmt = $pdo->prepare("INSERT INTO vouchers (code, user_id, event_name, template, valid_from, valid_to) VALUES (:code, :uid, :evt, :tpl, :vf, :vt)");
        $stmt->execute([
            ':code'=>$code, ':uid'=>$user_id ?: null, ':evt'=>$event_name, ':tpl'=>$template,
            ':vf'=>$valid_from ?: null, ':vt'=>$valid_to ?: null
        ]);
    }
    public static function all(Database $db) {
        $stmt = $db->pdo()->query("SELECT v.*, u.email, u.name FROM vouchers v LEFT JOIN users u ON u.id = v.user_id ORDER BY v.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function renderTemplate($template, $data=[]) {
        // reemplaza {{key}} en la plantilla por valores en $data
        $out = $template;
        foreach ($data as $k=>$v) {
            $out = str_replace('{{'.$k.'}}', htmlspecialchars($v), $out);
        }
        return $out;
    }
    public static function markAsUsed(Database $db, $id) {
        $stmt = $db->pdo()->prepare("UPDATE vouchers SET used=1 WHERE id = :id");
        $stmt->execute([':id'=>$id]);
    }
}
