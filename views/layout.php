<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .btn-turquoise {
            background: #20c997;
            color: #fff;
        }
        .btn-turquoise:hover {
            background: #17a589;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; color: #495057; }
        tr:hover { background: #f8f9fa; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #495057; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        input:focus, textarea:focus { outline: none; border-color: #007bff; }
        .actions { display: flex; gap: 10px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .empty { text-align: center; padding: 40px; color: #6c757d;}
        nav {
            background: #2c3e50;
            padding: 12px 20px;
        }

        nav a {
            color: #ecf0f1;
            text-decoration: none;
            margin-right: 20px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        /* CSS du dropdown pour les prix */
        .dropdown {
        position: relative;
        display: inline-block;
        }

        .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f1f1f1;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        }

        .checkbox-option {
        display: flex;
        align-items: center;
        color: black;
        padding: 12px 16px 12px 10px;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.3s;
        line-height: normal;
        }

        .checkbox-option:hover {
        background-color: #ddd;
        }

        .checkbox-option input[type="checkbox"] {
        margin-right: 8px;
        margin-left: 0;
        cursor: pointer;
        vertical-align: middle;
        transform: none;
        width: 16px;
        height: 16px;
        }

        .checkbox-option span {
        cursor: pointer;
        vertical-align: middle;
        line-height: normal;
        margin: 0;
        padding: 0;
        }

        .dropdown:hover .dropdown-content {
        display: block;
        }
    </style>
</head>
<body>

    <div class="container">
        <nav>
            <a href="<?= $baseUrl ?>/product">Produits</a>
            <a href="<?= $baseUrl ?>/cart">Paniers</a>
        </nav>

        <?php echo $content; ?>
    </div>
</body>
</html>

