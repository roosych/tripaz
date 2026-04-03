/**
 * Favorites — event delegation handler.
 * One listener on document handles all .favorite-btn clicks,
 * including dynamically rendered cards.
 */
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.favorite-btn');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    var wrap = btn.closest('.favorite-btn-wrap');
    if (!wrap) return;

    var authed   = wrap.dataset.authed === 'true';
    var loginUrl = wrap.dataset.loginUrl;

    if (!authed) {
        var loginModal = document.getElementById('loginModal');
        if (loginModal && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(loginModal).show();
        } else {
            window.location.href = loginUrl;
        }
        return;
    }

    var favorited  = wrap.dataset.favorited === 'true';
    var storeUrl   = wrap.dataset.storeUrl;
    var destroyUrl = wrap.dataset.destroyUrl;
    var icon       = btn.querySelector('i');
    var csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    btn.disabled = true;

    fetch(favorited ? destroyUrl : storeUrl, {
        method:  favorited ? 'DELETE' : 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept':       'application/json',
        },
    })
    .then(function (res) {
        if (res.ok || res.redirected) {
            var nowFavorited = !favorited;
            wrap.dataset.favorited = nowFavorited ? 'true' : 'false';

            if (nowFavorited) {
                icon.className = 'ph ph-heart-fill text-danger';
                btn.title      = 'Remove from favorites';
            } else {
                icon.className = 'ph ph-heart text-danger';
                btn.title      = 'Add to favorites';
            }
        }
    })
    .catch(function () { /* silent fail — button stays in current state */ })
    .finally(function () { btn.disabled = false; });
});
