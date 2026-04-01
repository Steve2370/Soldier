<canvas id="rain-canvas" style="position: fixed; inset: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none;"></canvas>

<script>
    (function() {
        const canvas = document.getElementById('rain-canvas');
        const ctx = canvas.getContext('2d');

        let W, H, drops = [];
        const COUNT = 120;

        function resize() {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }

        function randomDrop() {
            return {
                x: Math.random() * W,
                y: Math.random() * H * -1,
                speed: 0.4 + Math.random() * 1.2,
                radius: 0.8 + Math.random() * 1.4,
                opacity: 0.08 + Math.random() * 0.35,
                drift: (Math.random() - 0.5) * 0.15,
            };
        }

        function init() {
            resize();
            drops = Array.from({ length: COUNT }, randomDrop);
        }

        function draw() {
            ctx.clearRect(0, 0, W, H);

            for (const d of drops) {
                ctx.beginPath();
                ctx.arc(d.x, d.y, d.radius, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(255, 255, 255, ${d.opacity})`;
                ctx.fill();
                d.y += d.speed;
                d.x += d.drift;

                if (d.y > H + 5) {
                    d.y = -5;
                    d.x = Math.random() * W;
                }
            }

            requestAnimationFrame(draw);
        }

        window.addEventListener('resize', resize);
        init();
        draw();
    })();
</script>
