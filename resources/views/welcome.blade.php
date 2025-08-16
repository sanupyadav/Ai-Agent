<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Call Analysis AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 font-sans bg-gray-100 min-h-screen">

  <h1 class="text-3xl font-bold mb-6">📞 Call Analysis AI</h1>

  <form id="uploadForm" action="{{ route('upload.audio') }}" method="POST" enctype="multipart/form-data" class="mb-6">
    @csrf
    <label class="block mb-2 font-medium">Upload Call Audio (wav, mp3):</label>
    <input type="file" name="audio" accept="audio/*" required class="mb-4"/>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload & Transcribe</button>
  </form>

  <div id="transcript" class="mb-6 p-4 bg-white rounded shadow max-w-xl"></div>

  <form id="analyzeForm" style="display:none;">
    @csrf
    <input type="hidden" name="transcript" id="transcriptInput" />
    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Analyze Conversation</button>
  </form>

  <div id="analysisResult" class="mt-6 p-4 bg-white rounded shadow max-w-xl"></div>

<script>
const uploadForm = document.getElementById('uploadForm');
const analyzeForm = document.getElementById('analyzeForm');
const transcriptDiv = document.getElementById('transcript');
const transcriptInput = document.getElementById('transcriptInput');
const analysisResult = document.getElementById('analysisResult');

uploadForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(uploadForm);

  transcriptDiv.textContent = 'Transcribing audio, please wait...';
  analysisResult.textContent = '';
  analyzeForm.style.display = 'none';

  const res = await fetch(uploadForm.action, {
    method: 'POST',
    body: formData,
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
  });

  const data = await res.json();

  if (res.ok) {
    transcriptDiv.textContent = data.transcript;
    transcriptInput.value = data.transcript;
    analyzeForm.style.display = 'block';
  } else {
    transcriptDiv.textContent = 'Transcription failed: ' + data.message;
  }
});

analyzeForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  analysisResult.textContent = 'Analyzing conversation, please wait...';

  const formData = new FormData(analyzeForm);

  const res = await fetch('{{ route('analyze.conversation') }}', {
    method: 'POST',
    body: formData,
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
  });

  const data = await res.json();

  if (res.ok) {
    analysisResult.innerHTML = '<pre>' + JSON.stringify(data.analysis, null, 2) + '</pre>';
  } else {
    analysisResult.textContent = 'Analysis failed: ' + data.message;
  }
});
</script>

</body>
</html>
