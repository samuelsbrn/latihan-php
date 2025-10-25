<!DOCTYPE html>
<html>
<head>
    <title>Detail Todo - PHP Todo App</title>
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #4b86a2ff; }
        .card { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
<div class="container-fluid p-5">
    <div class="card">
        <div class="card-header">
            <h1>Detail Todo</h1>
        </div>
        <div class="card-body">
            <h3><?php echo htmlspecialchars($todo['title']); ?></h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($todo['description'] ?: 'No description'); ?></p>
            <p><strong>Status:</strong>
                <?php if ($todo['is_finished']): ?>
                    <span class="badge bg-success">Selesai</span>
                <?php else: ?>
                    <span class="badge bg-warning">Belum Selesai</span>
                <?php endif; ?>
            </p>
            <p><strong>Created At:</strong> <?php echo date('d F Y - H:i', strtotime($todo['created_at'])); ?></p>
            <p><strong>Updated At:</strong> <?php echo date('d F Y - H:i', strtotime($todo['updated_at'])); ?></p>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
</body>
</html>
