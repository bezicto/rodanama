<?php
// wheelofnames.php
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Roda Nama oleh Khairul Asyrani</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        textarea {
            width: 300px;
            height: 150px;
        }
        canvas {
            border: 1px solid #ccc;
            margin-top: 20px;
        }
        /* Gaya untuk modal */
        .modal {
            display: none; /* Tersembunyi secara default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <h1>Roda Nama v1.0.20250221</h1>
    <p>Masukkan nama (satu nama setiap baris):</p>
    <textarea id="namesInput" placeholder="Contoh:
Ali
Abu
Siti"></textarea>
    <br>
    <button id="startButton">Mula</button>
    <br>
    <canvas id="wheelCanvas" width="500" height="500"></canvas>

    <!-- Modal untuk paparan nama yang dipilih -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <p id="selectedNameText"></p>
            <button id="okButton">OK</button>
        </div>
    </div>

    <script>
    let names = [];
    let spinning = false;
    let angle = 0;
    let angularVelocity = 0;
    // Ubah nilai deceleration jika perlu (contoh: 0.005)
    let deceleration = 0.005;
    let spinAnimation;
    const pointerAngle = -Math.PI/2; // Pointer berada di atas (atan -90°)

    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');
    const startButton = document.getElementById('startButton');
    const okButton = document.getElementById('okButton');
    const modal = document.getElementById('modal');
    const selectedNameText = document.getElementById('selectedNameText');

    // Fungsi untuk melukis roda dengan segmen nama
    function drawWheel() {
        if (names.length === 0) return;
        const arcSize = 2 * Math.PI / names.length;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (let i = 0; i < names.length; i++) {
            const startAngle = angle + i * arcSize;
            const endAngle = startAngle + arcSize;
            ctx.fillStyle = getColor(i);
            ctx.beginPath();
            ctx.moveTo(canvas.width/2, canvas.height/2);
            ctx.arc(canvas.width/2, canvas.height/2, canvas.width/2 - 10, startAngle, endAngle);
            ctx.closePath();
            ctx.fill();
            
            // Lukis nama pada setiap segmen (teks di tengah segmen)
            ctx.save();
            ctx.translate(canvas.width/2, canvas.height/2);
            ctx.rotate(startAngle + arcSize/2);
            ctx.textAlign = "right";
            ctx.fillStyle = "#000";
            ctx.font = "16px Arial";
            ctx.fillText(names[i], canvas.width/2 - 20, 10);
            ctx.restore();
        }
        // Lukis penunjuk di bahagian atas
        drawPointer();
    }
    
    // Fungsi untuk menghasilkan warna bagi setiap segmen
    function getColor(index) {
        const colors = ['#FF5733', '#33FF57', '#3357FF', '#F333FF', '#FF33F3', '#33FFF3', '#F3FF33', '#FF8833'];
        return colors[index % colors.length];
    }
    
    // Fungsi untuk melukis penunjuk (pointer)
    function drawPointer() {
        ctx.fillStyle = "#000";
        ctx.beginPath();
        ctx.moveTo(canvas.width/2 - 10, 10);
        ctx.lineTo(canvas.width/2 + 10, 10);
        ctx.lineTo(canvas.width/2, 40);
        ctx.closePath();
        ctx.fill();
    }
    
    // Fungsi untuk memulakan putaran roda
    function startSpin() {
        if (names.length === 0) {
            alert("Sila masukkan sekurang-kurangnya satu nama.");
            return;
        }
        spinning = true;
        // Tetapkan angularVelocity secara rawak antara 0.3 hingga 0.6
        angularVelocity = Math.random() * 0.3 + 0.3;
        spinAnimation = requestAnimationFrame(rotate);
    }
    
    // Fungsi animasi putaran roda
    function rotate() {
        if (!spinning) return;
        angle += angularVelocity;
        angularVelocity -= deceleration;
        if (angularVelocity <= 0) {
            angularVelocity = 0;
            spinning = false;
            // Lukis keadaan terakhir roda
            drawWheel();
            // Kira segmen yang berada di bawah pointer
            const arcSize = 2 * Math.PI / names.length;
            // Pusat segmen pertama ialah (angle + arcSize/2)
            // Indeks nama yang dipilih dikira berdasarkan perbezaan antara pointer dan pusat segmen
            let diff = pointerAngle - (angle + arcSize/2);
            // Normalisasi kepada julat -PI hingga PI
            diff = ((diff + Math.PI) % (2 * Math.PI)) - Math.PI;
            let selectedIndex = Math.round(diff / arcSize);
            // Pastikan indeks dalam julat 0 hingga names.length - 1
            selectedIndex = ((selectedIndex % names.length) + names.length) % names.length;
            const selectedName = names[selectedIndex];
            selectedNameText.textContent = "Nama yang dipilih: " + selectedName;
            modal.style.display = "block";
            return;
        }
        drawWheel();
        spinAnimation = requestAnimationFrame(rotate);
    }
    
    // Event listener untuk butang 'Mula'
    startButton.addEventListener('click', () => {
        const input = document.getElementById('namesInput').value;
        names = input.split('\n').map(name => name.trim()).filter(name => name !== "");
        if (names.length === 0) {
            alert("Sila masukkan sekurang-kurangnya satu nama.");
            return;
        }
        // Lumpuhkan textarea dan butang mula semasa putaran
        document.getElementById('namesInput').disabled = true;
        startButton.disabled = true;
        // Tetapkan angle awal supaya pusat segmen pertama (names[0]) berada di bawah pointer
        const arcSize = 2 * Math.PI / names.length;
        angle = pointerAngle - arcSize/2;
        drawWheel();
        startSpin();
    });
    
    // Event listener untuk butang 'OK' pada modal
    okButton.addEventListener('click', () => {
        modal.style.display = "none"; // Tutup modal
        // Cari nama yang dipilih berdasarkan teks modal
        const selectedName = selectedNameText.textContent.replace("Nama yang dipilih: ", "");
        const indexToRemove = names.indexOf(selectedName);
        if (indexToRemove > -1) {
            names.splice(indexToRemove, 1);
        }
        if (names.length > 0) {
            // Tetapkan semula angle supaya segmen pertama bagi senarai baru berada di bawah pointer
            const arcSize = 2 * Math.PI / names.length;
            angle = pointerAngle - arcSize/2;
            drawWheel();
            setTimeout(startSpin, 1000);
        } else {
            alert("Semua nama telah dipilih.");
            document.getElementById('namesInput').disabled = false;
            startButton.disabled = false;
        }
    });
    
    // Lukisan awal roda
    drawWheel();
    </script>
</body>
</html>
