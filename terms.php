<?php
require 'db_conn.php';

$content = "";

try {
    // Get terms content from Database
    $stmt = $conn->prepare("SELECT setting_value FROM Settings WHERE setting_key = 'terms_content'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['setting_value'])) {
        $content = $result['setting_value'];
    } else {
        $content = "<div class='alert alert-warning text-center'>No terms content available. Please contact Admin.</div>";
    }

} catch (Exception $e) {
    $content = "<div class='alert alert-danger'>System Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Use - Uni Idea Hub</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background-color: #e9ecef; /* Darker background to highlight the paper */
            padding-top: 30px;
            padding-bottom: 30px;
        }
        .paper-doc {
            background: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            min-height: 80vh;
            display: flex;
            flex-direction: column;
        }
        .doc-header {
            text-align: center;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .doc-content {
            flex-grow: 1; /* Push button to bottom */
            font-size: 1.1rem;
            line-height: 1.8;
            color: #2c3e50;
        }
        /* Style for internal content (authored by Admin) */
        .doc-content h2, .doc-content h3 { color: #002B5B; margin-top: 25px; font-weight: 700; }
        .doc-content ul { padding-left: 20px; color: #555; }
        
        .doc-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        .btn-close-custom {
            background-color: #b82702;
            color: white;
            padding: 10px 40px;
            border-radius: 30px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-close-custom:hover {
            background-color: #495057;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="paper-doc">
            <div class="doc-header">
                <img src="img/greenwichlogo.png" width="150" alt="Logo" class="mb-3" style="mix-blend-mode: multiply;">
                <h2 class="fw-bold text-uppercase text-primary">Terms & Conditions</h2>
                <p class="text-muted small">Last updated: <?php echo date("d/m/Y"); ?></p>
            </div>

            <div class="doc-content">
                <?php echo $content; ?>
            </div>

            <div class="doc-footer">
                <button onclick="closeTerms()" class="btn btn-close-custom border-0 shadow-sm">
                    <i class="bi bi-x-circle-fill me-2"></i> CLOSE PAGE
                </button>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>

</body>
</html>