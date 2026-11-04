<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$mensagem = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isbn = (int)$_POST["isbn"];
    $edicao = (int)$_POST["edicao"];
    $custo = (float)str_replace(",", ".", $_POST["custo"]);
    $titulo = trim($_POST["titulo"]);
    $categoria = trim($_POST["categoria"]);
    $estoque = max(0, (int)$_POST["estoque"]);
    $descricao = trim($_POST["descricao"]);
    $nome_autor = trim($_POST["nome_autor"]);
    $nome_editora = trim($_POST["nome_editora"]);

    if ($isbn <= 0 || $titulo == "" || $custo <= 0) {
        $mensagem = "Preencha ISBN, título e um preço válido.";
        $tipo = "erro";
    } else {
        $stmt = $conexao->prepare("SELECT isbn FROM livros WHERE isbn = ?");
        $stmt->bind_param("i", $isbn);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($existe) {
            $mensagem = "Já existe um livro com este ISBN.";
            $tipo = "erro";
        } else {
            $foto = "";
            if (!empty($_FILES["foto"]["name"])) {
                $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
                if (in_array($ext, ["jpg", "jpeg", "png", "webp", "gif"])) {
                    $pasta = "fotos/";
                    if (!is_dir($pasta)) {
                        mkdir($pasta, 0777, true);
                    }
                    $foto = $pasta . $isbn . "." . $ext;
                    move_uploaded_file($_FILES["foto"]["tmp_name"], $foto);
                }
            }

            $stmt = $conexao->prepare("INSERT INTO livros (isbn, edição, custo, titulo, foto, categoria, descricao, estoque)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iidssssi", $isbn, $edicao, $custo, $titulo, $foto, $categoria, $descricao, $estoque);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                if ($nome_autor != "") {
                    $prox = (int)$conexao->query("SELECT COALESCE(MAX(codigo_autor), 0) + 1 AS prox FROM autores")->fetch_assoc()["prox"];
                    $stmt = $conexao->prepare("INSERT INTO autores (codigo_autor, autoria, nome_autor) VALUES (?, ?, ?)");
                    $stmt->bind_param("iis", $prox, $isbn, $nome_autor);
                    $stmt->execute();
                    $stmt->close();
                }
                if ($nome_editora != "") {
                    $prox = (int)$conexao->query("SELECT COALESCE(MAX(codigo_editora), 0) + 1 AS prox FROM editora")->fetch_assoc()["prox"];
                    $stmt = $conexao->prepare("INSERT INTO editora (codigo_editora, nome, publicacao) VALUES (?, ?, ?)");
                    $stmt->bind_param("isi", $prox, $nome_editora, $isbn);
                    $stmt->execute();
                    $stmt->close();
                }

                $mensagem = "Livro cadastrado com sucesso!";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao cadastrar o livro.";
                $tipo = "erro";
            }
        }
    }
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Cadastrar Livro</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <div class="formulario">
            <h1>Cadastrar livro</h1>
            <form method="POST" enctype="multipart/form-data">
                <label>ISBN</label>
                <div class="campo">
                    <span class="icone">🔢</span>
                    <input type="number" name="isbn" placeholder="9788595081512" required>
                </div>

                <button type="button" onclick="buscarLivro()" class="botao-google">🔍 Buscar título e sinopse pelo ISBN</button>

                <label>Título</label>
                <div class="campo">
                    <span class="icone">📖</span>
                    <input type="text" name="titulo" required>
                </div>

                <label>Autor</label>
                <div class="campo">
                    <span class="icone">✍️</span>
                    <input type="text" name="nome_autor">
                </div>

                <label>Editora</label>
                <div class="campo">
                    <span class="icone">🏢</span>
                    <input type="text" name="nome_editora">
                </div>

                <label>Categoria</label>
                <div class="campo">
                    <span class="icone">🏷️</span>
                    <input type="text" name="categoria" placeholder="Fantasia, Romance, Terror..." list="categorias">
                    <datalist id="categorias">
                        <option value="Fantasia">
                        <option value="Romance">
                        <option value="Terror">
                    </datalist>
                </div>

                <label>Edição</label>
                <div class="campo">
                    <span class="icone">🔢</span>
                    <input type="number" name="edicao" value="1" min="1">
                </div>

                <label>Preço (R$)</label>
                <div class="campo">
                    <span class="icone">💰</span>
                    <input type="text" name="custo" placeholder="95.99" required>
                </div>

                <label>Estoque</label>
                <div class="campo">
                    <span class="icone">📦</span>
                    <input type="number" name="estoque" value="10" min="0" required>
                </div>

                <label>Capa do livro (imagem)</label>
                <div class="campo">
                    <span class="icone">🖼️</span>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp,.gif">
                </div>

                <label>Sinopse</label>
                <textarea name="descricao" rows="5" placeholder="Sinopse do livro..."></textarea>

                <button type="submit">Cadastrar livro</button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>"><?= $mensagem ?></div>
            <?php endif; ?>

            <div class="rodape">
                <a href="listar_livros.php">← Voltar para livros</a>
            </div>
        </div>
    </main>

<div id="aviso-api" class="mensagem" style="display:none;"></div>

<script>
async function buscarLivro() {
    const campoIsbn = document.querySelector("[name=isbn]");
    const isbn = campoIsbn.value.replace(/\D/g, "");
    const aviso = document.getElementById("aviso-api");

    function mostrar(mensagem, tipo) {
        aviso.textContent = mensagem;
        aviso.className = "mensagem " + tipo;
        aviso.style.display = "block";
        aviso.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    function preencher(dados) {
        document.querySelector("[name=titulo]").value = dados.title || "";
        document.querySelector("[name=descricao]").value = dados.description || "";
        if (dados.authors && dados.authors.length) {
            document.querySelector("[name=nome_autor]").value = dados.authors[0];
        }
        if (dados.publishers && dados.publishers.length) {
            document.querySelector("[name=nome_editora]").value = dados.publishers[0];
        }
    }

    if (isbn.length < 10) {
        mostrar("Digite um ISBN válido (10 ou 13 números).", "erro");
        return;
    }

    aviso.className = "mensagem ok";
    aviso.textContent = "Buscando dados do livro...";
    aviso.style.display = "block";

    let dados = null;

    try {
        const r1 = await fetch("https://www.googleapis.com/books/v1/volumes?q=isbn:" + isbn);
        const j1 = await r1.json();
        if (j1.totalItems > 0) dados = j1.items[0].volumeInfo;
    } catch (e) {}

    if (!dados) {
        try {
            const r2 = await fetch("https://openlibrary.org/api/books?bibkeys=ISBN:" + isbn + "&format=json&jscmd=data");
            const j2 = await r2.json();
            const l = j2["ISBN:" + isbn];
            if (l) {
                dados = {
                    title: l.title,
                    authors: (l.authors || []).map(a => a.name),
                    publishers: (l.publishers || []).map(p => p.name),
                    description: l.notes || ""
                };
            }
        } catch (e) {}
    }

    if (!dados) {
        try {
            const r3 = await fetch("https://openlibrary.org/search.json?q=" + isbn + "&limit=1");
            const j3 = await r3.json();
            if (j3.docs && j3.docs.length > 0) {
                const d = j3.docs[0];
                dados = {
                    title: d.title,
                    authors: d.author_name || [],
                    publishers: d.publisher ? [d.publisher[0]] : [],
                    description: ""
                };
            }
        } catch (e) {}
    }

    if (!dados) {
        mostrar("ISBN não encontrado nas bases públicas. Confira se digitou o ISBN do livro impresso (não  e-ISBN) ou preencha manualmente.", "erro");
        return;
    }

    preencher(dados);
    mostrar("Dados preenchidos automaticamente. Confira e complete o que faltar.", "ok");
}
</script>
</body>
</html>
