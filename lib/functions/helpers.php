function setFlashMessage($type, $message) {
$_SESSION[$type . '_message'] = $message;
}