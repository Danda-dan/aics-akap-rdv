<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RDV</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body, html {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white; /* White to light pink gradient */
            color: #333;
        }
        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            background: #e0f7fa; /* Light blue background */
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .login-btn {
            background-color: #4A36FE; /* Light pink button */
            color: #fff;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        .login-btn:hover {
            background-color: #2201FF; /* Darker pink for hover */
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Record Duplicate Verification System</h1>
        <p>Your hub for verifying list of data efficiently. Sign in to access all features and ensure seamless operations.</p>
        <a href="{{ route('login') }}">
            <button class="login-btn">Login</button>
        </a>
    </div>
</body>
</html>
