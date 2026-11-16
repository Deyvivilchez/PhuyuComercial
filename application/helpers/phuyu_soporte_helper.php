<?php defined('BASEPATH') OR exit('No direct script access allowed');

function phuyu_url_grupo_soporte($valor)
{
    if (!is_string($valor)) { return ''; }
    $url = trim($valor);
    $partes = parse_url($url);
    if (!filter_var($url, FILTER_VALIDATE_URL) || !$partes
        || ($partes['scheme'] ?? '') !== 'https'
        || strtolower($partes['host'] ?? '') !== 'chat.whatsapp.com'
        || isset($partes['user']) || isset($partes['pass']) || isset($partes['port'])
        || !preg_match('~^/[A-Za-z0-9_-]+/?$~D', $partes['path'] ?? '')) {
        return '';
    }
    return $url;
}
