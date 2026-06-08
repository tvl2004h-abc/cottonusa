// ==================== GIỎ HÀNG ====================
window.getCart = function () {
    try {
        const cart = localStorage.getItem('cottonusa_cart');
        return cart ? JSON.parse(cart) : [];
    } catch (e) {
        console.error('Lỗi đọc cart:', e);
        return [];
    }
};

window.saveCart = function (cart) {
    localStorage.setItem('cottonusa_cart', JSON.stringify(cart));
    window.updateCartUI();
};

window.addToCart = function (product) {
    const cart = window.getCart();

    const existingIndex = cart.findIndex(item =>
        item.id === product.id &&
        item.size === product.size &&
        item.color === product.color
    );

    if (existingIndex !== -1) {
        cart[existingIndex].qty += product.qty;
    } else {
        cart.push({ ...product });
    }

    window.saveCart(cart);
};

window.removeFromCart = function (index) {
    const cart = window.getCart();

    if (index >= 0 && index < cart.length) {
        cart.splice(index, 1);
        window.saveCart(cart);
    }
};

window.formatPrice = function (price) {
    if (isNaN(price)) price = 0;
    return Number(price).toLocaleString('vi-VN') + '₫';
};

window.escapeHtml = function (str) {
    if (!str) return '';

    return str.replace(/[&<>]/g, function (m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
};

window.updateCartUI = function () {
    const cart = window.getCart();

    const totalItems = cart.reduce((sum, item) => {
        return sum + (item.qty || 1);
    }, 0);

    const cartBadge = document.getElementById('cartBadge');
    const cartItemCount = document.getElementById('cartItemCount');
    const cartItemsList = document.getElementById('cartItemsList');
    const cartFooter = document.getElementById('cartFooter');
    const cartTotal = document.getElementById('cartTotal');

    if (cartBadge) {
        if (totalItems > 0) {
            cartBadge.style.display = 'flex';
            cartBadge.textContent = totalItems > 99 ? '99+' : totalItems;
        } else {
            cartBadge.style.display = 'none';
        }
    }

    if (cartItemCount) {
        cartItemCount.textContent = totalItems + ' sản phẩm';
    }

    if (!cartItemsList) return;

    if (cart.length === 0) {
        cartItemsList.innerHTML = `
            <div class="cart-empty-msg">
                <i class="fas fa-shopping-bag"></i>
                Giỏ hàng trống
            </div>
        `;

        if (cartFooter) {
            cartFooter.style.display = 'none';
        }

        return;
    }

    let html = '';
    let total = 0;

    cart.forEach((item, idx) => {
        const qty = item.qty || 1;
        const subtotal = (item.price || 0) * qty;

        total += subtotal;

        html += `
            <div class="cart-item-row">
                <img class="cart-item-img" src="${item.img}" alt="${window.escapeHtml(item.name)}">

                <div class="cart-item-info">
                    <div class="cart-item-name">${window.escapeHtml(item.name)}</div>
                    <div class="cart-item-meta">
                        Size: ${item.size || 'M'} -
                        Màu: ${item.color || 'Đen'} -
                        x${qty}
                    </div>
                </div>

                <div class="cart-item-price">
                    ${window.formatPrice(subtotal)}
                </div>

                <button type="button" class="cart-item-remove" data-index="${idx}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
    });

    cartItemsList.innerHTML = html;

    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', function () {
            const index = parseInt(this.dataset.index);
            window.removeFromCart(index);
        });
    });

    if (cartFooter) {
        cartFooter.style.display = 'block';
    }

    if (cartTotal) {
        cartTotal.textContent = window.formatPrice(total);
    }
};

window.checkout = function () {
    const cart = window.getCart();

    if (cart.length === 0) {
        alert('Giỏ hàng trống!');
        return;
    }

    localStorage.setItem('cottonusa_checkout', JSON.stringify(cart));
    window.location.href = 'giaohang.html';
};

const USERS_KEY = 'cottonusa_users';

function loadUsers() {
    try {
        const users = localStorage.getItem(USERS_KEY);
        return users ? JSON.parse(users) : [];
    } catch (e) {
        return [];
    }
}

function saveUsers(users) {
    localStorage.setItem(USERS_KEY, JSON.stringify(users));
}

function getCurrentUser() {
    try {
        const user = localStorage.getItem('cottonusa_current_user');
        return user ? JSON.parse(user) : null;
    } catch (e) {
        return null;
    }
}

function setCurrentUser(user) {
    if (user) {
        localStorage.setItem('cottonusa_current_user', JSON.stringify(user));
    } else {
        localStorage.removeItem('cottonusa_current_user');
    }

    updateAuthUI();
}

window.doRegister = function () {
    const name = document.getElementById('regName')?.value.trim();
    const email = document.getElementById('regEmail')?.value.trim();
    const password = document.getElementById('regPassword')?.value.trim();

    const errorDiv = document.getElementById('registerError');

    if (!name || !email || !password) {
        errorDiv.textContent = 'Vui lòng nhập đầy đủ thông tin';
        errorDiv.style.display = 'block';
        return;
    }

    const users = loadUsers();

    if (users.find(u => u.email === email)) {
        errorDiv.textContent = 'Email đã tồn tại';
        errorDiv.style.display = 'block';
        return;
    }

    users.push({ name, email, password });
    saveUsers(users);

    setCurrentUser({ name, email });

    errorDiv.style.display = 'none';

    // Ferme le dropdown après inscription réussie
    const authDropdown = document.getElementById('authDropdown');
    const overlay = document.getElementById('dropdownOverlay');
    if (authDropdown) authDropdown.classList.remove('open');
    if (overlay) overlay.classList.remove('active');

    alert('Đăng ký thành công!');
};

window.doLogin = function () {
    const email = document.getElementById('loginEmail')?.value.trim();
    const password = document.getElementById('loginPassword')?.value.trim();

    const errorDiv = document.getElementById('loginError');

    const users = loadUsers();

    const user = users.find(u => u.email === email && u.password === password);

    if (!user) {
        errorDiv.textContent = 'Sai email hoặc mật khẩu';
        errorDiv.style.display = 'block';
        return;
    }

    setCurrentUser({
        name: user.name,
        email: user.email
    });

    errorDiv.style.display = 'none';

    // Ferme le dropdown après connexion réussie
    const authDropdown = document.getElementById('authDropdown');
    const overlay = document.getElementById('dropdownOverlay');
    if (authDropdown) authDropdown.classList.remove('open');
    if (overlay) overlay.classList.remove('active');

    alert('Đăng nhập thành công!');
};

window.doLogout = function () {
    setCurrentUser(null);

    // Ferme le dropdown après déconnexion
    const authDropdown = document.getElementById('authDropdown');
    const overlay = document.getElementById('dropdownOverlay');
    if (authDropdown) authDropdown.classList.remove('open');
    if (overlay) overlay.classList.remove('active');

    alert('Đã đăng xuất');
};

window.switchTab = function (tab) {
    const loginPanel = document.getElementById('panelLogin');
    const registerPanel = document.getElementById('panelRegister');

    if (tab === 'login') {
        loginPanel.style.display = 'block';
        registerPanel.style.display = 'none';
    } else {
        loginPanel.style.display = 'none';
        registerPanel.style.display = 'block';
    }

    // Met à jour la classe active sur les boutons onglets
    document.querySelectorAll('.auth-tab').forEach(function (btn) {
        btn.classList.toggle('active', btn.getAttribute('onclick') === "switchTab('" + tab + "')");
    });
};

function updateAuthUI() {
    const user = getCurrentUser();

    const loggedOut = document.getElementById('authLoggedOut');
    const loggedIn = document.getElementById('authLoggedIn');

    if (!loggedOut || !loggedIn) return;

    if (user) {
        loggedOut.style.display = 'none';
        loggedIn.style.display = 'block';

        document.getElementById('authAvatar').textContent = user.name.charAt(0).toUpperCase();
        document.getElementById('authUsername').textContent = user.name;
        document.getElementById('authUserEmail').textContent = user.email;
    } else {
        loggedOut.style.display = 'block';
        loggedIn.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    window.updateCartUI();
    updateAuthUI();
});
