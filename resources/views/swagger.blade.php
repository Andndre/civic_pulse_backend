<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPulse API Documentation</title>
    <!-- Modern Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Swagger UI Styles -->
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            background: #0f172a; /* Slate 900 for modern dark mode base */
            font-family: 'Outfit', sans-serif;
            color: #f1f5f9;
        }
        /* Premium custom styling to override default Swagger UI aesthetics */
        .swagger-ui {
            font-family: 'Outfit', sans-serif !important;
        }
        .swagger-ui .topbar {
            background-color: #1e293b !important; /* Slate 800 */
            border-bottom: 1px solid #334155;
            padding: 12px 0;
        }
        .swagger-ui .topbar .download-url-wrapper input[type=text] {
            border: 2px solid #3b82f6 !important; /* Premium Blue */
            border-radius: 6px;
            background: #0f172a;
            color: #f1f5f9;
            padding: 6px 10px;
        }
        .swagger-ui .topbar .download-url-button {
            background: #3b82f6 !important;
            border-radius: 6px;
            color: #fff;
            padding: 6px 15px;
            font-weight: 600;
        }
        .swagger-ui .info {
            margin: 40px 0 !important;
        }
        .swagger-ui .info .title {
            color: #3b82f6 !important;
            font-size: 36px;
            font-weight: 700;
        }
        .swagger-ui .info .title small {
            background-color: #3b82f6 !important;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 14px;
        }
        .swagger-ui .info li, 
        .swagger-ui .info p, 
        .swagger-ui .info a {
            color: #94a3b8 !important; /* Slate 400 */
            font-size: 15px;
        }
        .swagger-ui .scheme-container {
            background: #1e293b !important;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: 1px solid #334155;
            margin: 20px 0 !important;
            padding: 20px !important;
        }
        .swagger-ui .btn.authorize {
            background-color: transparent !important;
            border-color: #10b981 !important; /* Emerald 500 */
            color: #10b981 !important;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .swagger-ui .btn.authorize:hover {
            background-color: #10b981 !important;
            color: #fff !important;
        }
        .swagger-ui .btn.authorize svg {
            fill: #10b981 !important;
        }
        .swagger-ui .btn.authorize:hover svg {
            fill: #fff !important;
        }
        .swagger-ui .opblock {
            border-radius: 10px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #334155 !important;
            margin-bottom: 12px !important;
        }
        .swagger-ui .opblock.opblock-post { background: rgba(16, 185, 129, 0.05) !important; }
        .swagger-ui .opblock.opblock-get { background: rgba(59, 130, 246, 0.05) !important; }
        .swagger-ui .opblock.opblock-put { background: rgba(245, 158, 11, 0.05) !important; }
        .swagger-ui .opblock.opblock-patch { background: rgba(139, 92, 246, 0.05) !important; }
        .swagger-ui .opblock.opblock-delete { background: rgba(239, 68, 68, 0.05) !important; }
        
        .swagger-ui .opblock-summary {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        .swagger-ui .opblock .opblock-summary-method {
            border-radius: 6px !important;
            font-weight: 700 !important;
        }
        .swagger-ui .opblock-description-wrapper p,
        .swagger-ui .opblock-title_normal,
        .swagger-ui .tabli button,
        .swagger-ui label,
        .swagger-ui .response-col_status,
        .swagger-ui .response-col_links,
        .swagger-ui .parameter__name,
        .swagger-ui .parameter__type,
        .swagger-ui .parameter__in {
            color: #cbd5e1 !important; /* Slate 300 */
        }
        .swagger-ui table thead tr td, 
        .swagger-ui table thead tr th {
            color: #cbd5e1 !important;
            border-bottom: 1px solid #334155 !important;
        }
        .swagger-ui .parameters-col_description input[type=text],
        .swagger-ui .parameters-col_description select,
        .swagger-ui textarea {
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #f1f5f9 !important;
            border-radius: 6px;
            padding: 8px !important;
        }
        .swagger-ui .model-box {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            border-radius: 8px;
            padding: 10px !important;
        }
        .swagger-ui section.models {
            border: 1px solid #334155 !important;
            border-radius: 12px;
            background: #1e293b;
        }
        .swagger-ui section.models h4 {
            color: #cbd5e1 !important;
            border-bottom: 1px solid #334155 !important;
        }
        .swagger-ui .model-title {
            color: #3b82f6 !important;
        }
        .swagger-ui .model {
            color: #cbd5e1 !important;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                persistAuthorization: true
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
