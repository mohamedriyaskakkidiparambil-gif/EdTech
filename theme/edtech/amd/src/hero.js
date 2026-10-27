// theme/edtech/amd/src/hero.js
// Accessible, dependency-free homepage hero slider.
define([], function() {

    var init = function() {
        var root = document.querySelector('[data-edt-slider]');
        if (!root) {
            return;
        }

        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-edt-slide]'));
        var dots = Array.prototype.slice.call(root.querySelectorAll('[data-edt-slider-dot]'));
        var previous = root.querySelector('[data-edt-slider-prev]');
        var next = root.querySelector('[data-edt-slider-next]');
        var toggle = root.querySelector('[data-edt-slider-toggle]');
        var toggleIcon = root.querySelector('[data-edt-slider-toggle-icon]');
        var status = root.querySelector('[data-edt-slider-status]');
        var interval = parseInt(root.getAttribute('data-interval'), 10) || 6000;
        var current = 0;
        var timer = null;
        var hovered = false;
        var focused = false;
        var hidden = document.hidden;
        var userPaused = false;
        var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (slides.length < 2) {
            if (previous) { previous.hidden = true; }
            if (next) { next.hidden = true; }
            if (toggle) { toggle.hidden = true; }
            if (dots.length) { dots[0].hidden = true; }
            return;
        }

        var shouldPause = function() {
            return hovered || focused || hidden || userPaused || reducedMotion;
        };

        var updateToggle = function() {
            if (!toggle || !toggleIcon) {
                return;
            }
            var paused = shouldPause();
            var label = paused ? toggle.getAttribute('data-play-label') : toggle.getAttribute('data-pause-label');
            toggle.setAttribute('aria-label', label);
            toggleIcon.className = paused ? 'fa-solid fa-play' : 'fa-solid fa-pause';
        };

        var syncTimer = function() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
            if (!shouldPause()) {
                timer = window.setInterval(function() {
                    showSlide(current + 1, false);
                }, interval);
            }
            updateToggle();
        };

        var showSlide = function(index, announce) {
            current = (index + slides.length) % slides.length;

            slides.forEach(function(slide, slideIndex) {
                var active = slideIndex === current;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });

            dots.forEach(function(dot, dotIndex) {
                var active = dotIndex === current;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            if (status && (announce || !status.textContent)) {
                status.textContent = slides[current].getAttribute('data-status-label') || '';
            }
        };

        var move = function(offset) {
            showSlide(current + offset, true);
            syncTimer();
        };

        if (previous) {
            previous.addEventListener('click', function() { move(-1); });
        }
        if (next) {
            next.addEventListener('click', function() { move(1); });
        }
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                showSlide(parseInt(dot.getAttribute('data-slide-index'), 10) || 0, true);
                syncTimer();
            });
        });

        if (toggle) {
            toggle.addEventListener('click', function() {
                userPaused = !userPaused;
                syncTimer();
            });
        }

        root.addEventListener('mouseenter', function() {
            hovered = true;
            syncTimer();
        });
        root.addEventListener('mouseleave', function() {
            hovered = false;
            syncTimer();
        });
        root.addEventListener('focusin', function() {
            focused = true;
            syncTimer();
        });
        root.addEventListener('focusout', function(event) {
            if (!root.contains(event.relatedTarget)) {
                focused = false;
                syncTimer();
            }
        });
        root.addEventListener('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                move(-1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                move(1);
            }
        });

        document.addEventListener('visibilitychange', function() {
            hidden = document.hidden;
            syncTimer();
        });

        showSlide(0, false);
        syncTimer();
    };

    return {
        init: init
    };
});
