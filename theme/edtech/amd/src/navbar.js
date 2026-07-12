// theme/edtech/amd/src/navbar.js
// Handles user-menu dropdown and topbar language-switcher pill.
// Pure JS — no Bootstrap JS dependency.
define([], function() {

    var initUserMenu = function() {
        var toggle = document.getElementById('user-menu-toggle');
        var menu   = document.getElementById('user-action-menu');
        if (!toggle || !menu) { return; }

        var parent = toggle.closest('.dropdown');

        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var isOpen = parent.classList.contains('show');

            // Close every other open dropdown first.
            document.querySelectorAll('.dropdown.show').forEach(function(d) {
                d.classList.remove('show');
                var m = d.querySelector('.dropdown-menu');
                if (m) { m.classList.remove('show'); }
            });

            if (!isOpen) {
                parent.classList.add('show');
                menu.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
            } else {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('click', function(e) {
            if (parent && !parent.contains(e.target)) {
                parent.classList.remove('show');
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    };

    var initLangPill = function() {
        var pill     = document.getElementById('edtLangPill');
        var dropdown = document.getElementById('edtLangDropdown');
        if (!pill || !dropdown) { return; }

        var toggleLang = function(open) {
            pill.setAttribute('aria-expanded', open ? 'true' : 'false');
            dropdown.classList.toggle('open', open);
        };

        pill.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleLang(!dropdown.classList.contains('open'));
        });

        pill.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleLang(!dropdown.classList.contains('open'));
            }
            if (e.key === 'Escape') { toggleLang(false); }
        });

        document.addEventListener('click', function() { toggleLang(false); });
        dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
    };

    return {
        init: function() {
            initUserMenu();
            initLangPill();
        }
    };
});
