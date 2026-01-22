<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Error Page</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            margin: 0 auto;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .section {
            text-align: center;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .section-1 {
            flex: 1;
            min-height: 200px;
        }

        .section-2 {
            flex: 2;
            min-height: 400px;
        }

        .section-3 {
            flex: 1;
            min-height: 200px;
        }

        p {
            font-size: 1.3rem;
            color: #205678;
            font-weight: bold;
            line-height: 1.5;
            max-width: 600px;
            margin: 0 10px 14px 10px;
        }

        .error-image {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .btn-theme {
            background-color: #205678;
            color: #fff;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            height: 20px;
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn-theme:hover {
            background-color: #1a4660;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
                flex-direction: column;
                gap: 20px;
            }
            
            .section {
                min-height: auto;
            }
            
            .section-2 {
                order: 1;
            }
            
            .section-1 {
                order: 2;
            }
            
            .section-3 {
                order: 3;
            }
            
            h3 {
                font-size: 1.2rem;
            }
            
            .btn-theme {
                min-width: 180px;
                padding: 12px 30px;
            }
        }
            
    </style>
</head>

<body>
    <div class="container">
        <!-- Section 1: Home Button -->
        <div class="section section-1">
            <a href="{{ env('APP_URL') }}" class="btn btn-theme">Home</a>
        </div>
        
        <!-- Section 2: Message and Image -->
        <div class="section section-2">
            <p>⚠️ Access Restricted <br>
                Your current subscription does not include the Website Management Feature.<br>
                To continue, you'll need to:<br>
                1) Upgrade to a plan that includes this Website Management Feature, or<br>
                2) Purchase the Website Management Feature Add-On.</p>
            <img class="error-image" src="{{ url('images/400.svg') }}" alt="400 error">
        </div>
        
        <!-- Section 3: Login Button -->
        <div class="section section-3">
            <a href="{{ url('/login') }}" class="btn btn-theme">Login</a>
        </div>
    </div>
</body>

</html>
