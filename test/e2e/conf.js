exports.config = {
    seleniumAddress: 'http://localhost:4444/wd/hub',
    specs: [
        'login-spec.js',
        'about-us-spec.js'
    ]
};