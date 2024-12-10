function initGroq(config) {
    const promptButton = document.getElementById("prompt-button");
    const messageInput = document.getElementById("message-input");
    const chatMessages = document.getElementById("chat-messages");

    promptButton.addEventListener("click", function () {
        console.log("Clicked prompt button");
        
        const content = messageInput.value.trim();
        if (content) {
            fetch(config.groqPromptUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": config.csrfToken,
                },
                body: JSON.stringify({ content: content }),
            })
                .then((response) => response.json())
                .then((data) => {
                    console.log("data received:", data);
                    
                    // Create a new message element
                    const messageElement = document.createElement("div");
                    messageElement.className = "message";
                    messageElement.innerHTML = `
                    <strong>Groq AI</strong>
                    <span class="input-field">${new Date().toLocaleString()}</span>
                    <div class="purple">${data.choices[0].message.content}</div>
                `;

                    // Append the new message
                    chatMessages.appendChild(messageElement);

                    // Scroll to the bottom of the chat
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                })
                .catch((error) => {
                    console.error("Error:", error);
                });

            // Clear the input after sending
            messageInput.value = "";
        }
    });
}
