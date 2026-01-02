document.addEventListener("DOMContentLoaded", () => {
    // --- Element Selections ---
    const firstNameInput = document.getElementById("first_name_input"); // New
    const lastNameInput = document.getElementById("last_name_input");   // New
    const phoneInput = document.getElementById("phone_num");
    const phoneError = document.getElementById("phoneError");
    const usernameInput = document.getElementById("username");
    const usernameError = document.getElementById("usernameError");
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirm_password");
    const passMatchError = document.getElementById("passwordMatchError");
    const passComplexityError = document.getElementById("passwordComplexityError");
    const registrationForm = document.querySelector('form');
    const submitBtn = document.querySelector("button[type='submit']");

    // --- State Variables to track user interaction ---
    let firstNameInteracted = false; // New
    let lastNameInteracted = false;  // New
    let phoneInteracted = false;
    let usernameInteracted = false;

    // --- Utility to manage Bootstrap validation classes ---
    const setValidationState = (input, isValid) => {
        // Only apply validation classes if the field has content OR if a valid state is requested
        if (input.value.length > 0 || isValid) {
             input.classList.toggle('is-invalid', !isValid);
             input.classList.toggle('is-valid', isValid);
        } else {
             input.classList.remove('is-invalid', 'is-valid');
        }
    };

    // --- Generic Name Validation Function ---
    function validateNameField(input, interacted) {
        const value = input.value.trim();
        const regex = /^[a-zA-Z]{2,}$/; // Min 2 letters, no numbers/special chars
        const isValid = regex.test(value);

        if (interacted) {
            setValidationState(input, isValid);
        } else {
            input.classList.remove('is-invalid', 'is-valid');
        }
        
        return isValid;
    }

    // --- 1. Password Visibility Toggle ---
    document.querySelectorAll(".eye-toggle").forEach(toggle => {
        toggle.addEventListener("click", () => {
            const input = document.getElementById(toggle.dataset.target);
            const icon = toggle.querySelector("i");
            
            const isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";
            
            icon.classList.toggle("bi-eye-slash", !isPassword);
            icon.classList.toggle("bi-eye", isPassword);
        });
    });

    // --- 2. Password Complexity Check ---
    function checkPasswordComplexity(password) {
        const errors = [];
        if (password.length > 0) {
            if (password.length < 8) errors.push("minimum 8 characters");
            if (!/[A-Z]/.test(password)) errors.push("one uppercase letter (A-Z)");
            if (!/[a-z]/.test(password)) errors.push("one lowercase letter (a-z)");
            if (!/[0-9]/.test(password)) errors.push("one number (0-9)");
            // Special character check: Non-word/non-space character
            if (!/[^\w\s]/.test(password)) errors.push("one special character"); 
        }

        const complexityValid = errors.length === 0 && password.length > 0;

        if (passComplexityError) {
            if (password.length > 0 && !complexityValid) {
                passComplexityError.textContent = "Must contain: " + errors.join(", ") + ".";
                passComplexityError.classList.remove("d-none");
                setValidationState(passwordInput, false);
            } else {
                passComplexityError.classList.add("d-none");
            }
        }
        return complexityValid;
    }

    // --- 3. Real-Time Password Match Check ---
    function checkPasswords() {
        const p1 = passwordInput.value;
        const p2 = confirmInput.value;
        const isMatch = p1 === p2;
        const complexityOk = checkPasswordComplexity(p1);
        const bothSet = p1.length > 0 && p2.length > 0;

        // Visual Feedback for Confirm Password field
        if (p2.length > 0) {
            setValidationState(confirmInput, isMatch);
        } else {
            confirmInput.classList.remove('is-invalid', 'is-valid');
        }

        // Visual Feedback for Password Input based on complexity AND match
        if (p1.length > 0) {
            if (complexityOk) {
                // If complexity is OK, only validate if confirm field is also being used
                if (p2.length > 0) {
                    setValidationState(passwordInput, isMatch);
                } else {
                    // Password input is valid if complexity is met and confirm is empty
                    setValidationState(passwordInput, true); 
                }
            } else {
                 setValidationState(passwordInput, false); 
            }
        } else {
             passwordInput.classList.remove('is-invalid', 'is-valid');
        }

        // Error message visibility (Match Error)
        if (bothSet && !isMatch) {
            passMatchError.textContent = "Passwords do not match";
            passMatchError.classList.remove("d-none");
        } else {
            passMatchError.classList.add("d-none");
        }

        // Final result: both must be set, match, and meet complexity
        return isMatch && bothSet && complexityOk;
    }

    // --- 4. Main Form Validity Check and Button Toggle ---
    function checkFormValidity() {
        // 4a. Name Validations
        const firstNameValid = validateNameField(firstNameInput, firstNameInteracted);
        const lastNameValid = validateNameField(lastNameInput, lastNameInteracted);

        // 4b. Phone Validation (09XXXXXXXXX)
        const phoneRegex = /^09\d{9}$/;
        const phoneValue = phoneInput.value.trim();
        const phoneValid = phoneRegex.test(phoneValue);
        const phoneEmpty = phoneValue === "";
        
        if (phoneInteracted) {
             phoneError.classList.toggle("d-none", phoneValid || phoneEmpty);
             if (!phoneValid && !phoneEmpty) {
                 phoneError.textContent = "Invalid phone number. Must be 09XXXXXXXXX (11 digits).";
             }
             setValidationState(phoneInput, phoneValid);
        } else {
             phoneError.classList.add("d-none");
             phoneInput.classList.remove('is-invalid', 'is-valid');
        }

        // 4c. Username Validation (4-20 chars, alphanumeric/underscore)
        const usernameRegex = /^[a-zA-Z0-9_]{4,20}$/;
        const usernameValue = usernameInput.value.trim();
        const usernameValid = usernameRegex.test(usernameValue);
        const usernameEmpty = usernameValue === "";

        if (usernameInteracted) {
             usernameError.classList.toggle("d-none", usernameValid || usernameEmpty);
             setValidationState(usernameInput, usernameValid);
        } else {
             usernameError.classList.add("d-none");
             usernameInput.classList.remove('is-invalid', 'is-valid');
        }

        // 4d. Password Match & Complexity Check
        const passwordsOk = checkPasswords();

        // 4e. Final Submit Button State
        // checkValidity() automatically handles required fields for all inputs
        const allFieldsValid = registrationForm.checkValidity(); 
        
        submitBtn.disabled = !(
            firstNameValid &&      // Check First Name
            lastNameValid &&       // Check Last Name
            phoneValid && 
            usernameValid && 
            passwordsOk && 
            allFieldsValid
        );
    }
    
    // --- 5. Attach Event Listeners ---

    // Set interaction flag on first input, then run validation
    firstNameInput.addEventListener("input", () => {
        firstNameInteracted = true;
        checkFormValidity();
    });
    lastNameInput.addEventListener("input", () => {
        lastNameInteracted = true;
        checkFormValidity();
    });
    phoneInput.addEventListener("input", () => {
        phoneInteracted = true;
        checkFormValidity();
    });
    usernameInput.addEventListener("input", () => {
        usernameInteracted = true;
        checkFormValidity();
    });

    // Password fields always trigger validation
    passwordInput.addEventListener("input", checkFormValidity);
    confirmInput.addEventListener("input", checkFormValidity);

    // Initial state: Disable button
    checkFormValidity();
    submitBtn.disabled = true; 
});