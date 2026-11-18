<?php

function fotos_pasta() {
    $dir = __DIR__ . "/fotos";

    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    return $dir;
}

function foto_slug($texto) {
    $texto = preg_replace('/[^A-Za-z0-9]+/', '_', trim((string)$texto));
    return trim($texto, "_");
}

function foto_erro_txt($codigo) {
    switch ($codigo) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "A imagem é maior que o limite do servidor.";
        case UPLOAD_ERR_PARTIAL:
            return "O envio foi interrompido. Tente novamente.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "O servidor não tem pasta temporária configurada.";
        case UPLOAD_ERR_CANT_WRITE:
            return "O servidor não conseguiu gravar o arquivo.";
        case UPLOAD_ERR_EXTENSION:
            return "O envio foi bloqueado pelo servidor.";
        default:
            return "Falha no envio da imagem.";
    }
}

function foto_reduzir($caminho, $max = 700) {
    if (!function_exists("imagecreatefromstring")) {
        return;
    }

    $dados = @file_get_contents($caminho);

    if (!$dados) {
        return;
    }

    $img = @imagecreatefromstring($dados);

    if (!$img) {
        return;
    }

    $larg = imagesx($img);
    $alt  = imagesy($img);

    if ($larg <= $max && $alt <= $max) {
        imagedestroy($img);
        return;
    }

    $escala    = min($max / $larg, $max / $alt);
    $nova_larg = max(1, (int)round($larg * $escala));
    $nova_alt  = max(1, (int)round($alt * $escala));

    $nova   = imagecreatetruecolor($nova_larg, $nova_alt);
    $branco = imagecolorallocate($nova, 255, 255, 255);
    imagefill($nova, 0, 0, $branco);
    imagecopyresampled($nova, $img, 0, 0, 0, 0, $nova_larg, $nova_alt, $larg, $alt);

    $info = @getimagesize($caminho);
    $mime = $info["mime"] ?? "";

    if ($mime === "image/png") {
        @imagepng($nova, $caminho, 8);
    } elseif ($mime === "image/webp" && function_exists("imagewebp")) {
        @imagewebp($nova, $caminho, 85);
    } elseif ($mime === "image/gif") {
        @imagegif($nova, $caminho);
    } else {
        @imagejpeg($nova, $caminho, 85);
    }

    imagedestroy($nova);
    imagedestroy($img);
}

function salvar_foto($arquivo, $chave = "", &$erro = null) {
    $erro = "";

    if (!is_array($arquivo) || !isset($arquivo["error"])) {
        return "";
    }

    if ((int)$arquivo["error"] === UPLOAD_ERR_NO_FILE) {
        return "";
    }

    if ((int)$arquivo["error"] !== UPLOAD_ERR_OK) {
        $erro = foto_erro_txt((int)$arquivo["error"]);
        return "";
    }

    if (!is_uploaded_file($arquivo["tmp_name"])) {
        $erro = "Envio inválido.";
        return "";
    }

    $tamanho = (int)($arquivo["size"] ?? 0);

    if ($tamanho <= 0) {
        $erro = "O arquivo está vazio.";
        return "";
    }

    if ($tamanho > 5 * 1024 * 1024) {
        $erro = "A imagem deve ter no máximo 5 MB.";
        return "";
    }

    $info = @getimagesize($arquivo["tmp_name"]);

    if (!$info || empty($info["mime"])) {
        $erro = "O arquivo não é uma imagem válida.";
        return "";
    }

    $extensoes = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
        "image/gif"  => "gif"
    ];

    $ext = $extensoes[$info["mime"]] ?? "";

    if ($ext === "") {
        $erro = "Formato não aceito. Use JPG, PNG, WEBP ou GIF.";
        return "";
    }

    $base = foto_slug($chave);

    if ($base === "") {
        $base = "livro";
    }

    $nome    = strtolower($base) . "_" . substr(md5(uniqid("", true)), 0, 8) . "." . $ext;
    $destino = fotos_pasta() . "/" . $nome;

    if (!@move_uploaded_file($arquivo["tmp_name"], $destino)) {
        $erro = "Não foi possível salvar. Verifique a permissão da pasta fotos.";
        return "";
    }

    @chmod($destino, 0664);
    foto_reduzir($destino, 700);

    return "fotos/" . $nome;
}

function foto_url($foto) {
    $foto = trim(str_replace("\\", "/", (string)$foto));

    if ($foto === "") {
        return "";
    }

    if (preg_match('#^https?://#i', $foto)) {
        return $foto;
    }

    $foto = ltrim($foto, "/");

    if (is_file(__DIR__ . "/" . $foto)) {
        return $foto;
    }

    $base = basename($foto);

    if ($base === "") {
        return "";
    }

    if (is_file(fotos_pasta() . "/" . $base)) {
        return "fotos/" . $base;
    }

    return "";
}

function foto_remover($foto) {
    $url = foto_url($foto);

    if ($url === "" || preg_match('#^https?://#i', $url)) {
        return false;
    }

    $caminho = __DIR__ . "/" . $url;

    return is_file($caminho) ? @unlink($caminho) : false;
}