const provinceSelect = document.getElementById('province');
const citySelect = document.getElementById('city');
const barangaySelect = document.getElementById('barangay');
const contactInput = document.getElementById('contact');
const contactError = document.getElementById('contactError');
const BASE_URL = 'https://psgc.gitlab.io/api';

const savedProv = document.body.dataset.province || '';
const savedCity = document.body.dataset.city || '';
const savedBrgy = document.body.dataset.barangay || '';

async function loadProvinces() {
    const res = await fetch(`${BASE_URL}/provinces/`);
    const provinces = await res.json();
    provinces.sort((a,b)=>a.name.localeCompare(b.name));
    provinceSelect.innerHTML = '<option value="">Select Province</option>';
    provinces.forEach(p => provinceSelect.innerHTML += `<option value="${p.name}">${p.name}</option>`);
}

async function loadCities(provName) {
    if(!provName){ citySelect.innerHTML='<option value="">Select City</option>'; return; }
    const res = await fetch(`${BASE_URL}/provinces/`);
    const provinces = await res.json();
    const province = provinces.find(p=>p.name===provName);
    if(!province) return;
    const citiesRes = await fetch(`${BASE_URL}/provinces/${province.code}/cities-municipalities/`);
    const cities = await citiesRes.json();
    cities.sort((a,b)=>a.name.localeCompare(b.name));
    citySelect.innerHTML='<option value="">Select City</option>';
    cities.forEach(c => citySelect.innerHTML += `<option value="${c.name}">${c.name}</option>`);
}

async function loadBarangays(cityName) {
    if(!cityName){ barangaySelect.innerHTML='<option value="">Select Barangay</option>'; return; }
    const provRes = await fetch(`${BASE_URL}/provinces/`);
    const provinces = await provRes.json();
    let cityCode = null;
    for(const p of provinces){
        const citiesRes = await fetch(`${BASE_URL}/provinces/${p.code}/cities-municipalities/`);
        const cities = await citiesRes.json();
        const city = cities.find(c=>c.name===cityName);
        if(city){ cityCode = city.code; break; }
    }
    if(!cityCode) return;
    const barRes = await fetch(`${BASE_URL}/cities-municipalities/${cityCode}/barangays/`);
    const barangays = await barRes.json();
    barangays.sort((a,b)=>a.name.localeCompare(b.name));
    barangaySelect.innerHTML='<option value="">Select Barangay</option>';
    barangays.forEach(b => barangaySelect.innerHTML += `<option value="${b.name}">${b.name}</option>`);
}

provinceSelect.addEventListener('change', e=>{ loadCities(e.target.value); barangaySelect.innerHTML='<option value="">Select Barangay</option>'; });
citySelect.addEventListener('change', e=>{ loadBarangays(e.target.value); });

window.addEventListener('DOMContentLoaded', async ()=>{
    await loadProvinces();
    if(savedProv){ provinceSelect.value = savedProv; await loadCities(savedProv); }
    if(savedCity){ citySelect.value = savedCity; await loadBarangays(savedCity); }
    if(savedBrgy){ barangaySelect.value = savedBrgy; }
});

// ------------------ STRICT VALIDATION ------------------
document.getElementById('checkoutForm').addEventListener('submit', function(e){
    let isValid = true;
    
    // Selects ALL fields with the 'required' attribute
    const requiredInputs = this.querySelectorAll('[required]');
    
    requiredInputs.forEach(input => {
        // .trim() handles the "only white spaces" scenario
        if (input.value.trim() === "") {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Contact Number Regex Validation
    const contactVal = contactInput.value.trim();
    if(!/^09\d{9}$/.test(contactVal)){
        isValid = false;
        contactError.style.display = 'block';
        contactInput.classList.add('is-invalid');
    } else {
        contactError.style.display = 'none';
    }

    if (!isValid) {
        e.preventDefault();
        alert("Please fill in all required fields. Spaces only are not allowed.");
        
        // Focus on the first invalid field
        const firstInvalid = document.querySelector('.is-invalid');
        if(firstInvalid) firstInvalid.focus();
    }
});