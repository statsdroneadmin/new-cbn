// Configuration
const CONFIG = {
    ageRequirement: 18, // Default age requirement
    jurisdictionAgeMap: {
        'US': 21, // United States
        'GB': 18, // United Kingdom
        'CA': 19, // Canada (varies by province)
        'AU': 18, // Australia
        'DE': 18, // Germany
        'FR': 18, // France
        'ES': 18, // Spain
        'IT': 18, // Italy
    },
    apiEndpoint: 'api/submit.php' // Backend endpoint
};

// Detect user's jurisdiction and set age requirement
async function detectJurisdiction() {
    try {
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();
        const countryCode = data.country_code;
        
        if (CONFIG.jurisdictionAgeMap[countryCode]) {
            CONFIG.ageRequirement = CONFIG.jurisdictionAgeMap[countryCode];
            document.getElementById('ageRequirement').textContent = CONFIG.ageRequirement;
        }
        
        return {
            ip: data.ip,
            country: data.country_name,
            countryCode: countryCode,
            city: data.city,
            region: data.region,
            timezone: data.timezone
        };
    } catch (error) {
        console.error('Error detecting jurisdiction:', error);
        return null;
    }
}

// Collect technical data
function collectTechnicalData() {
    return {
        userAgent: navigator.userAgent,
        browser: getBrowserInfo(),
        deviceType: getDeviceType(),
        operatingSystem: getOperatingSystem(),
        screenResolution: `${window.screen.width}x${window.screen.height}`,
        viewport: `${window.innerWidth}x${window.innerHeight}`,
        colorDepth: window.screen.colorDepth,
        language: navigator.language,
        languages: navigator.languages.join(','),
        platform: navigator.platform,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        timestamp: new Date().toISOString(),
        referrer: document.referrer || 'direct',
        utmParams: getUTMParameters()
    };
}

// Get browser information
function getBrowserInfo() {
    const ua = navigator.userAgent;
    let browser = 'Unknown';
    let version = '';

    if (ua.indexOf('Firefox') > -1) {
        browser = 'Firefox';
        version = ua.match(/Firefox\/([0-9.]+)/)?.[1] || '';
    } else if (ua.indexOf('Chrome') > -1 && ua.indexOf('Edg') === -1) {
        browser = 'Chrome';
        version = ua.match(/Chrome\/([0-9.]+)/)?.[1] || '';
    } else if (ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1) {
        browser = 'Safari';
        version = ua.match(/Version\/([0-9.]+)/)?.[1] || '';
    } else if (ua.indexOf('Edg') > -1) {
        browser = 'Edge';
        version = ua.match(/Edg\/([0-9.]+)/)?.[1] || '';
    } else if (ua.indexOf('Opera') > -1 || ua.indexOf('OPR') > -1) {
        browser = 'Opera';
        version = ua.match(/(?:Opera|OPR)\/([0-9.]+)/)?.[1] || '';
    }

    return `${browser} ${version}`;
}

// Get device type
function getDeviceType() {
    const ua = navigator.userAgent;
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
        return 'Tablet';
    }
    if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
        return 'Mobile';
    }
    return 'Desktop';
}

// Get operating system
function getOperatingSystem() {
    const ua = navigator.userAgent;
    const platform = navigator.platform;

    if (ua.indexOf('Win') > -1) return 'Windows';
    if (ua.indexOf('Mac') > -1) return 'macOS';
    if (ua.indexOf('Linux') > -1) return 'Linux';
    if (ua.indexOf('Android') > -1) return 'Android';
    if (ua.indexOf('like Mac') > -1) return 'iOS';
    
    return platform || 'Unknown';
}

// Get UTM parameters from URL
function getUTMParameters() {
    const params = new URLSearchParams(window.location.search);
    return {
        source: params.get('utm_source') || null,
        medium: params.get('utm_medium') || null,
        campaign: params.get('utm_campaign') || null,
        term: params.get('utm_term') || null,
        content: params.get('utm_content') || null
    };
}

// Cookie management
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Strict`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function acceptCookies() {
    setCookie('cookieConsent', 'accepted', 365);
    setCookie('trackingConsent', 'accepted', 365);
    document.getElementById('cookieConsent').style.display = 'none';
    initializeTracking();
}

function declineCookies() {
    setCookie('cookieConsent', 'declined', 365);
    setCookie('trackingConsent', 'declined', 365);
    document.getElementById('cookieConsent').style.display = 'none';
}

function initializeTracking() {
    // Initialize analytics or tracking scripts here if needed
    console.log('Tracking initialized');
}

// Check cookie consent on page load
window.addEventListener('DOMContentLoaded', () => {
    const consent = getCookie('cookieConsent');
    if (consent) {
        document.getElementById('cookieConsent').style.display = 'none';
        if (consent === 'accepted') {
            initializeTracking();
        }
    }
    
    // Detect jurisdiction
    detectJurisdiction();
    
    // Show/hide SMS consent based on phone input
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            const smsConsentGroup = document.getElementById('smsConsentGroup');
            if (phoneInput.value.trim().length > 0) {
                smsConsentGroup.style.display = 'block';
            } else {
                smsConsentGroup.style.display = 'none';
                document.getElementById('smsConsent').checked = false;
            }
        });
    }
});

// Form validation
function validateForm() {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    
    // First Name
    const firstName = document.getElementById('firstName').value.trim();
    if (!firstName) {
        document.getElementById('firstNameError').textContent = 'First name is required';
        isValid = false;
    } else if (firstName.length < 2) {
        document.getElementById('firstNameError').textContent = 'First name must be at least 2 characters';
        isValid = false;
    }
    
    // Email
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        document.getElementById('emailError').textContent = 'Email is required';
        isValid = false;
    } else if (!emailRegex.test(email)) {
        document.getElementById('emailError').textContent = 'Please enter a valid email address';
        isValid = false;
    }
    
    // Phone (if provided)
    const phone = document.getElementById('phone').value.trim();
    if (phone) {
        const phoneRegex = /^[0-9\s\-\(\)]+$/;
        if (!phoneRegex.test(phone) || phone.replace(/\D/g, '').length < 6) {
            document.getElementById('phoneError').textContent = 'Please enter a valid phone number';
            isValid = false;
        }
        
        // Check SMS consent if phone is provided
        const smsConsent = document.getElementById('smsConsent');
        if (!smsConsent.checked) {
            document.getElementById('phoneError').textContent = 'Please consent to SMS communications or remove phone number';
            isValid = false;
        }
    }
    
    // Age Verification
    const ageVerification = document.getElementById('ageVerification');
    if (!ageVerification.checked) {
        document.getElementById('ageError').textContent = `You must be at least ${CONFIG.ageRequirement} years old`;
        isValid = false;
    }
    
    // Email Consent
    const emailConsent = document.getElementById('emailConsent');
    if (!emailConsent.checked) {
        document.getElementById('emailConsentError').textContent = 'You must consent to receive emails';
        isValid = false;
    }
    
    return isValid;
}

// Handle form submission
async function handleSubmit(event) {
    event.preventDefault();
    
    if (!validateForm()) {
        return false;
    }
    
    // Show loading indicator
    document.getElementById('loadingIndicator').style.display = 'block';
    document.querySelector('.submit-btn').disabled = true;
    
    try {
        // Collect all data
        const formData = {
            firstName: document.getElementById('firstName').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            countryCode: document.getElementById('countryCode').value,
            ageVerified: document.getElementById('ageVerification').checked,
            emailConsent: document.getElementById('emailConsent').checked,
            smsConsent: document.getElementById('smsConsent').checked,
            language: document.getElementById('languageSelect').value,
            technicalData: collectTechnicalData(),
            jurisdiction: await detectJurisdiction()
        };
        
        // Send to backend
        const response = await fetch(CONFIG.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Store email and IDs in session storage for confirmation page
            sessionStorage.setItem('userEmail', formData.email);
            sessionStorage.setItem('userId', result.userId);
            sessionStorage.setItem('shortId', result.shortId);

            // Redirect to survey page
            window.location.href = 'survey.html';
        } else {
            alert('Error: ' + (result.message || 'Failed to submit form. Please try again.'));
            document.getElementById('loadingIndicator').style.display = 'none';
            document.querySelector('.submit-btn').disabled = false;
        }
    } catch (error) {
        console.error('Submission error:', error);
        alert('An error occurred. Please try again later.');
        document.getElementById('loadingIndicator').style.display = 'none';
        document.querySelector('.submit-btn').disabled = false;
    }
    
    return false;
}

// Language change (placeholder - will use translations.js)
function changeLanguage(lang) {
    // This will be implemented in translations.js
    console.log('Language changed to:', lang);
}
