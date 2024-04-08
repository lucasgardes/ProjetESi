window.onload = function() {
    var isLoggedIn = false;

    if (isLoggedIn) {
        document.getElementById('login-signup-container').style.display = 'none';
        document.getElementById('user-dropdown').style.display = 'block';
    } else {
        document.getElementById('login-signup-container').style.display = 'block';
        document.getElementById('user-dropdown').style.display = 'none';
    }
};
