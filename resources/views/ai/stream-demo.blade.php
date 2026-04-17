<!DOCTYPE html>
<html>

<head>
    <title>AI Streaming Demo</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        #response {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            min-height: 100px;
            white-space: pre-wrap;
            line-height: 1.6;
        }

        button {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:disabled {
            background: #93c5fd;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <h1>🤖 AI Streaming Demo</h1>
    <button id="btn" onclick="startStream()">Ask about BMW X5</button>
    <br><br>
    <div id="response">Click the button to start...</div>

    <script>
        function startStream() {
            const btn = document.getElementById('btn');
            const responseDiv = document.getElementById('response');

            btn.disabled = true;
            btn.textContent = 'Streaming...';
            responseDiv.textContent = '';

            const eventSource = new EventSource('/stream');

            eventSource.onmessage = function(event) {
                if (event.data === '[DONE]') {
                    eventSource.close();
                    btn.disabled = false;
                    btn.textContent = 'Ask about BMW X5';
                    return;
                }

                const data = JSON.parse(event.data);
                responseDiv.textContent += data.text;
            };

            eventSource.onerror = function() {
                eventSource.close();
                btn.disabled = false;
                btn.textContent = 'Ask about BMW X5';
            };
        }
    </script>
</body>

</html>
