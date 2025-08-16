<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Pay1 AI Agent</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-purple-100 min-h-screen flex font-sans">

  <!-- Sidebar -->
  <div id="sidebar" class="w-72 backdrop-blur-md bg-white/60 rounded-r-xl shadow-lg p-6 space-y-6 transition-all duration-300">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold text-red-600 flex items-center gap-2">🔧 Pay1 Config</h2>
      <button id="toggleSidebar" class="text-sm text-blue-600 underline hover:text-blue-800">Hide</button>
    </div>

    <div class="space-y-4">
      <!-- Provider -->
      <div>
        <label for="provider" class="block text-sm font-medium text-gray-700">🧠 Provider</label>
        <select id="provider" class="w-full mt-1 rounded-md px-4 py-2 border border-gray-300 focus:ring-2 focus:ring-red-500">
          <option value="ollama">Ollama</option>
          {{-- <option value="openai">OpenAI</option> --}}
          <option value="gpt4o">GPT4o</option>
          <option value="gpt41">GPT4.1</option>
          <option value="grok">GROK-3</option>
          <option value="new">A4F-3</option>
        </select>
      </div>

      <!-- Model -->
      <div>
        <label for="model" class="block text-sm font-medium text-gray-700">🧬 Model</label>
        <select id="model" class="w-full mt-1 rounded-md px-4 py-2 border border-gray-300 focus:ring-2 focus:ring-red-500"></select>
      </div>
    </div>

    <!-- File Upload -->
    <div>
      <label class="block text-sm font-medium text-gray-700">📂 Upload File</label>
      <form id="uploadForm" class="mt-1 flex items-center gap-2 max-w-full" enctype="multipart/form-data" novalidate>
        <input
          type="file"
          name="file"
          id="fileInput"
          class="flex-grow min-w-0 text-sm border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
          accept=".pdf,.docx,.txt"
        />
        <button
          type="submit"
          class="flex-shrink-0 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-blue-700 transition"
        >
          Upload
        </button>
      </form>
      <div
        id="uploadStatus"
        class="mt-2 text-sm font-medium min-h-[1.5rem] transition-opacity duration-300 opacity-0"
      ></div>
    </div>

  </div>

  <!-- Main Chat -->
  <div class="flex-1 flex flex-col p-6">
    <button id="expandSidebar" class="absolute top-4 left-4 bg-red-600 text-white text-xs px-3 py-1 rounded shadow hidden">
      Show Config
    </button>

    <div class="flex flex-col flex-grow bg-white/60 backdrop-blur-xl shadow-xl rounded-xl p-6 max-w-3xl mx-auto w-full">
      <div class="sticky top-0 z-10 mb-4">
        <h2 class="text-2xl font-bold text-center text-red-700">🤖 Ask <span class="text-gray-800">Pay1 AI Agent</span></h2>
      </div>

      <div id="chat" class="flex-grow overflow-y-auto space-y-4 border rounded-lg p-4 bg-white/60 max-h-[500px] scroll-smooth shadow-inner"></div>

      <form id="chatForm" class="mt-4 flex flex-col gap-2">
        <input type="text" id="query" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500" placeholder="Type your question..." />
        <button type="submit" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white py-2 rounded-lg font-semibold shadow">
          🚀 Ask
        </button>
      </form>
    </div>
  </div>

  <script>
    const chat = document.getElementById('chat');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('query');
    const providerSelect = document.getElementById('provider');
    const modelSelect = document.getElementById('model');
    const button = form.querySelector('button');
    const sidebar = document.getElementById('sidebar');
    const toggleSidebarBtn = document.getElementById('toggleSidebar');
    const expandSidebarBtn = document.getElementById('expandSidebar');

    // Replace this with your backend injected JSON models list
    const models = @json($models);

    function renderModels(provider) {
      modelSelect.innerHTML = '';
      models[provider].forEach(model => {
        const option = document.createElement('option');
        option.value = model.value;
        option.textContent = model.label;
        modelSelect.appendChild(option);
      });
    }

    toggleSidebarBtn.addEventListener('click', () => {
      sidebar.style.display = 'none';
      expandSidebarBtn.classList.remove('hidden');
    });

    expandSidebarBtn.addEventListener('click', () => {
      sidebar.style.display = 'block';
      expandSidebarBtn.classList.add('hidden');
    });

    providerSelect.addEventListener('change', () => {
      localStorage.setItem('selectedProvider', providerSelect.value);
      renderModels(providerSelect.value);
      localStorage.setItem('selectedModel', modelSelect.value);
    });

    modelSelect.addEventListener('change', () => {
      localStorage.setItem('selectedModel', modelSelect.value);
    });

    // Restore selection from localStorage
    const savedProvider = localStorage.getItem('selectedProvider') || 'ollama';
    const savedModel = localStorage.getItem('selectedModel');
    providerSelect.value = savedProvider;
    renderModels(savedProvider);
    if (savedModel) modelSelect.value = savedModel;

    function typeEffect(container, html, speed = 15) {
      return new Promise(resolve => {
        let i = 0;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const text = tempDiv.textContent || tempDiv.innerText || "";

        const interval = setInterval(() => {
          container.textContent += text.charAt(i);
          i++;
          if (i >= text.length) {
            clearInterval(interval);
            resolve();
          }
        }, speed);
      });
    }

    const sessionId = 'sess-' + Date.now() + '-' + Math.random().toString(36).substring(2, 10);

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const query = input.value.trim();
      const model = modelSelect.value;
      const provider = providerSelect.value;

      if (!query) return;

      button.disabled = true;
      button.innerText = 'Thinking...';

      chat.innerHTML += `
        <div class="flex items-start gap-2">
          <div class="text-xl">👤</div>
          <div class="bg-white rounded-lg px-4 py-2 shadow">${query}</div>
        </div>
      `;
      chat.scrollTop = chat.scrollHeight;

      input.value = '';

      const agentDiv = document.createElement('div');
      agentDiv.className = 'flex items-start gap-2';
      agentDiv.innerHTML = `
        <div><img src="https://cdn-icons-png.flaticon.com/512/4712/4712106.png" class="h-6 w-6" alt="Agent" /></div>
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-2 shadow w-full">
          <strong>Agent</strong>
          <span id="typing" class="block whitespace-pre-line text-sm text-gray-800 mt-1"></span>
        </div>
      `;
      chat.appendChild(agentDiv);
      chat.scrollTop = chat.scrollHeight;
      const typingSpan = agentDiv.querySelector('#typing');

      try {
        const res = await fetch('/api/ask-agent', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ query, model, provider, session_id: sessionId })
        });

        const data = await res.json();
        const html = marked.parse(data.reply);
        typingSpan.innerHTML = '';
        await typeEffect(typingSpan, html);
        chat.scrollTop = chat.scrollHeight;
      } catch (err) {
        typingSpan.innerHTML = `<span class="text-red-500">❌ Error: Could not get response.</span>`;
        chat.scrollTop = chat.scrollHeight;
      }

      button.disabled = false;
      button.innerText = '🚀 Ask';
    });

    // Upload File Handling
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const uploadStatus = document.getElementById('uploadStatus');

    let uploadTimeout;

    function showUploadMessage(message, type = 'info') {
      const colors = {
        info: 'text-blue-600 bg-blue-100 px-3 py-1 rounded',
        success: 'text-green-700 bg-green-100 px-3 py-1 rounded',
        error: 'text-red-700 bg-red-100 px-3 py-1 rounded',
      };
      clearTimeout(uploadTimeout);
      uploadStatus.textContent = message;
      uploadStatus.className = `mt-2 text-sm font-medium min-h-[1.5rem] transition-opacity duration-300 ${colors[type]}`;
      uploadStatus.style.opacity = 1;

      // Auto-hide after 5 seconds
      uploadTimeout = setTimeout(() => {
        clearUploadMessage();
      }, 5000);
    }

    function clearUploadMessage() {
      uploadStatus.style.opacity = 0;
      setTimeout(() => {
        uploadStatus.textContent = '';
        uploadStatus.className = 'mt-2 text-sm font-medium min-h-[1.5rem] transition-opacity duration-300 opacity-0';
      }, 300);
    }

    uploadForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!fileInput.files.length) {
        showUploadMessage('⚠️ Please select a file.', 'error');
        return;
      }

      clearUploadMessage();
      showUploadMessage('Uploading & embedding...', 'info');

      const formData = new FormData();
      formData.append('file', fileInput.files[0]);
      formData.append('provider', providerSelect.value);

      try {
        const res = await fetch('/api/upload-file', {
          method: 'POST',
          body: formData
        });

        const data = await res.json();

        if (res.ok) {
          showUploadMessage(data.message || '✅ Upload successful!', 'success');
        } else {
          showUploadMessage(data.message || '❌ Upload failed.', 'error');
        }
      } catch (err) {
        showUploadMessage('❌ Upload failed. Please try again.', 'error');
      }
    });
  </script>
</body>
</html>
