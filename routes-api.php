<?php
header('Content-Type: application/json');
http_response_code(410);
echo json_encode(['error' => 'This endpoint is no longer in use. All pricing is served from routes.json.']);
