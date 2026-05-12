<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Car Assistant</title>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: sans-serif;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background: #f9fafb;
        }

        #messages {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            min-height: 300px;
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 16px;
        }

        .message {
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .message.user {
            background: #3b82f6;
            color: white;
            margin-left: 20%;
            text-align: right;
        }

        .message.assistant {
            background: #f3f4f6;
            color: #111827;
            margin-right: 20%;
        }

        .input-row {
            display: flex;
            gap: 8px;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        button:disabled {
            background: #93c5fd;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <h2>🚗 Car Assistant</h2>

    <div x-data="chat()">

        <div id="messages">

            <template x-for="(message, $index) in messages" :key="$index">

                <div class="message" :class="message.role" x-text="message.text"></div>

            </template>

            <div x-show="streaming" style="color: #6b7280; font-size: 13px;">
                ● AI is typing...
            </div>

        </div>

        <div class="input-row">

            <input type="text" x-model="input" @keydown.enter="sendMessage()" placeholder="Ask about a car...">

            <button @click="sendMessage()" :disabled="streaming">Send</button>

        </div>

    </div>

    <script>
        function chat() {
            return {
                messages: [],
                input: '',
                streaming: false,
                sendMessage() {
                    if (!this.input.trim() || this.streaming) return;

                    const message = this.input.trim();

                    this.messages.push({
                        role: 'user',
                        text: message
                    });

                    this.input = '';

                    this.messages.push({
                        role: 'assistant',
                        text: ''
                    });

                    this.streaming = true;

                    const source = new EventSource('/stream?message=' + encodeURIComponent(message));

                    source.onmessage = (event) => {
                        if (event.data === '[DONE]') {
                            source.close();
                            this.streaming = false;
                            return;
                        }

                        const data = JSON.parse(event.data);

                        this.messages[this.messages.length - 1].text += data.text;
                    };

                    source.onerror = () => {
                        source.close();
                        this.streaming = false;
                    };
                }
            }
        }
    </script>

</body>

</html>
