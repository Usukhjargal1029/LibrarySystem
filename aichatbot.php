<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Load Composer autoload
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    } else {
        throw new Exception('Composer autoload not found');
    }

    // Load .env
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    // Get API key
    $apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? '';
    if (empty($apiKey)) {
        throw new Exception('OpenAI API key not configured');
    }

    // Get and validate input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $userMessage = trim($input['message'] ?? '');
    $userId = intval($input['user_id'] ?? 0);

    if (empty($userMessage)) {
        throw new Exception('Message cannot be empty');
    }

    // Limit message length
    $userMessage = substr($userMessage, 0, 1000);

    // Database configuration
    $dbConfig = [
        'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost',
        'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '',
        'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'library_db'
    ];

    // Connect to database
    $mysqli = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );

    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }

    // Set charset to prevent encoding issues
    $mysqli->set_charset('utf8mb4');

    // Initialize data arrays
    $borrowedBooks = [];
    $topRatedBooks = [];

    // Get borrowed books for the user
    if ($userId > 0) {
        $borrowedQuery = "
            SELECT DISTINCT b.title
            FROM borrowed_books bb
            INNER JOIN books b ON bb.book_id = b.id
            WHERE bb.user_id = ? AND bb.return_date IS NULL
            ORDER BY b.title
        ";
        
        $stmt = $mysqli->prepare($borrowedQuery);
        if (!$stmt) {
            error_log("Failed to prepare borrowed books query: " . $mysqli->error);
        } else {
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $borrowedBooks[] = $row['title'];
                }
            } else {
                error_log("Failed to execute borrowed books query: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    // Get top-rated books
    $topRatedQuery = "
        SELECT b.title, AVG(br.rating) as avg_rating, COUNT(br.rating) as rating_count
        FROM books b
        INNER JOIN book_ratings br ON b.id = br.book_id
        GROUP BY b.id, b.title
        HAVING rating_count >= 1
        ORDER BY avg_rating DESC, rating_count DESC
        LIMIT 5
    ";

    $stmt = $mysqli->prepare($topRatedQuery);
    if (!$stmt) {
        error_log("Failed to prepare top rated query: " . $mysqli->error);
    } else {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $topRatedBooks[] = $row['title'];
            }
        } else {
            error_log("Failed to execute top rated query: " . $stmt->error);
        }
        $stmt->close();
    }

    // Close database connection
    $mysqli->close();

    // Prepare context for AI
    $borrowedList = empty($borrowedBooks) ? "None" : implode(", ", $borrowedBooks);
    $topRatedList = empty($topRatedBooks) ? "None available" : implode(", ", $topRatedBooks);

    // Create system prompt
    $systemPrompt = "You are a helpful library assistant for a school library system.

CURRENT USER CONTEXT:
- Currently borrowed books: {$borrowedList}
- Top-rated books available: {$topRatedList}

YOUR RESPONSIBILITIES:
1. Help with book recommendations, library policies, and general library questions
2. When recommending books:
   - NEVER recommend books the user currently has borrowed
   - Prioritize books from the top-rated list when possible
   - If user has borrowed books, acknowledge them first
   - Suggest 2-3 different books for variety

LIBRARY INFORMATION:
- Borrowing period: 14 days from checkout
- Late fees: \$1 per day after due date
- Library hours: Monday to Friday, 9:00 AM to 5:00 PM
- Students can borrow up to 3 books at a time

RESPONSE GUIDELINES:
- Be friendly, helpful, and encouraging about reading
- Keep responses concise but informative
- If asked about topics unrelated to the library, politely redirect to library matters
- Always respond in the same language the user uses
- If asked about your AI model or technical details, answer honestly but briefly";

    // Prepare OpenAI API request
    $requestData = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ],
        'max_tokens' => 300,
        'temperature' => 0.7
    ];

    // Make API request
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($response === false) {
        throw new Exception('API request failed: ' . curl_error($ch));
    }
    
    curl_close($ch);

    // Parse response
    $responseData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid API response format');
    }

    // Handle API errors
    if ($httpCode !== 200) {
        $errorMsg = $responseData['error']['message'] ?? 'Unknown API error';
        throw new Exception("OpenAI API Error (HTTP $httpCode): $errorMsg");
    }

    // Extract AI response
    if (!isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception('No valid response from AI');
    }

    $aiReply = trim($responseData['choices'][0]['message']['content']);
    
    if (empty($aiReply)) {
        $aiReply = "I'm sorry, I couldn't generate a proper response. Please try asking your question again.";
    }

    // Return successful response
    echo json_encode([
        'success' => true,
        'reply' => $aiReply,
        'debug' => [
            'user_id' => $userId,
            'borrowed_count' => count($borrowedBooks),
            'top_rated_count' => count($topRatedBooks)
        ]
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Library Assistant Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'reply' => 'Sorry, I encountered an error while processing your request. Please try again.',
        'error' => $e->getMessage()
    ]);
}