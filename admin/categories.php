$categories = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY name
")->fetchAll();