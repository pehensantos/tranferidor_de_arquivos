<?php
// Diretório para armazenar os arquivos enviados.
$uploadDir = 'uploads/';

// Cria o diretório de uploads se ele não existir.
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$message = '';
$message_type = ''; // 'success' ou 'danger'

// Função para formatar o tamanho do arquivo de bytes para um formato legível.
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Processa o upload de arquivos.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    if ($_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['fileToUpload']['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFilePath)) {
            $message = "O arquivo '" . htmlspecialchars($fileName) . "' foi enviado com sucesso.";
            $message_type = 'success';
        } else {
            $message = 'Ocorreu um erro ao mover o arquivo enviado.';
            $message_type = 'danger';
        }
    } else {
        $message = 'Ocorreu um erro no upload. Código: ' . $_FILES['fileToUpload']['error'];
        $message_type = 'danger';
    }
}

// Processa a exclusão de arquivos.
if (isset($_GET['delete'])) {
    // Medida de segurança para evitar path traversal.
    $fileToDelete = basename($_GET['delete']);
    $filePath = $uploadDir . $fileToDelete;

    if (file_exists($filePath) && is_file($filePath)) {
        if (unlink($filePath)) {
            $message = "O arquivo '" . htmlspecialchars($fileToDelete) . "' foi excluído com sucesso.";
            $message_type = 'success';
        } else {
            $message = 'Não foi possível excluir o arquivo.';
            $message_type = 'danger';
        }
    } else {
        $message = 'Arquivo não encontrado.';
        $message_type = 'danger';
    }
}

// Lista os arquivos no diretório de uploads.
$files = array_diff(scandir($uploadDir), ['.', '..']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferência de Arquivos Local</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; }
        .card { margin-top: 2rem; }
        .table-responsive { margin-top: 1.5rem; }
        .alert { margin-top: 1rem; }
        .delete-btn {
            font-size: 0.9rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center">
            <h1 class="display-5">Transferência de Arquivos</h1>
            <p class="lead">Envie arquivos do seu celular para o computador ou baixe-os.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5>Enviar Arquivo</h5>
            </div>
            <div class="card-body">
                <form action="index.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fileToUpload" class="form-label">Selecione o arquivo para enviar:</label>
                        <input class="form-control" type="file" name="fileToUpload" id="fileToUpload" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Arquivos no Servidor</h5>
            </div>
            <div class="card-body">
                <?php if (empty($files)): ?>
                    <p class="text-center text-muted">Nenhum arquivo encontrado.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Nome do Arquivo</th>
                                    <th scope="col">Tamanho</th>
                                    <th scope="col" class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $file): ?>
                                    <?php $filePath = $uploadDir . $file; ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($filePath); ?>" download><?php echo htmlspecialchars($file); ?></a>
                                        </td>
                                        <td><?php echo formatBytes(filesize($filePath)); ?></td>
                                        <td class="text-end">
                                            <a href="index.php?delete=<?php echo urlencode($file); ?>" 
                                               class="btn btn-danger delete-btn"
                                               onclick="return confirm('Tem certeza que deseja excluir este arquivo?');">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>