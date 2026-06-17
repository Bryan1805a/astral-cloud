<!-- PARTICLE NETWORK BACKGROUND -->
<canvas id="particleCanvas" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none;"></canvas>

<script>
(function() {
    var canvas = document.getElementById('particleCanvas');
    var ctx = canvas.getContext('2d');
    var particles = [];
    var mouse = { x: -1000, y: -1000 };
    var PARTICLE_COUNT = 60;
    var CONNECT_DIST = 140;
    var LINE_COLOR = 'rgba(255, 255, 255, 0.18)';
    var PARTICLE_COLOR = 'rgba(255, 255, 255, 0.7)';

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    document.addEventListener('mousemove', function(e) {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    function Particle() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.vx = (Math.random() - 0.5) * 0.6;
        this.vy = (Math.random() - 0.5) * 0.6;
        this.radius = Math.random() * 2.5 + 1;
    }

    Particle.prototype.update = function() {
        this.x += this.vx;
        this.y += this.vy;
        if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
        if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
        this.vx += (Math.random() - 0.5) * 0.03;
        this.vy += (Math.random() - 0.5) * 0.03;
        var speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
        var maxSpeed = 1.0;
        if (speed > maxSpeed) { this.vx *= maxSpeed / speed; this.vy *= maxSpeed / speed; }
    };

    for (var i = 0; i < PARTICLE_COUNT; i++) {
        particles.push(new Particle());
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];
            p.update();

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = PARTICLE_COLOR;
            ctx.fill();

            var dx = mouse.x - p.x;
            var dy = mouse.y - p.y;
            var dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < CONNECT_DIST) {
                var alpha = 1 - (dist / CONNECT_DIST);
                ctx.beginPath();
                ctx.moveTo(mouse.x, mouse.y);
                ctx.lineTo(p.x, p.y);
                ctx.strokeStyle = 'rgba(125,211,252,' + (alpha * 0.25).toFixed(2) + ')';
                ctx.lineWidth = 1;
                ctx.stroke();
            }
        }

        requestAnimationFrame(draw);
    }

    draw();
})();
</script>

<!-- HERO -->
<section class="page-section hero-section">
    <div class="hero-content">
        <div>
            <h1>Cloud VPS that makes your <br><span>PROJECT</span><br>FASTER!</h1>
            <p>Astral Cloud provides high-performance VPS, fast deployment, stable security, and easy management for students, developers, and businesses.</p>
            <div class="hero-buttons">
                <a href="/plans#plans" class="btn-white">View VPS Plans</a>
                <a href="/register" class="btn-outline">Get Started</a>
            </div>
        </div>
    </div>
    <div class="hero-visual">
        <div class="orb orb-one"></div>
        <div class="server-shape">
            <span></span><span></span><span></span>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="page-section about-section">
    <h2>Why Astral Cloud?</h2>
    <p>We simulate a modern VPS rental and management system, helping users find server packages, place orders, make demo payments, and track activation status.</p>
    <div class="stats">
        <div class="stat-card"><span></span><h3>99.9%</h3><p>Uptime</p><small>Always-on infrastructure for stable VPS performance.</small></div>
        <div class="stat-card"><span></span><h3>NVMe</h3><p>SSD Storage</p><small>Ultra-fast storage for websites, APIs and applications.</small></div>
        <div class="stat-card"><span></span><h3>24/7</h3><p>Support</p><small>Professional assistance whenever you need help.</small></div>
        <div class="stat-card"><span></span><h3>DDoS</h3><p>Protection</p><small>Advanced protection against malicious attacks.</small></div>
    </div>
</section>
