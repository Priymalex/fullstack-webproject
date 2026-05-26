$('.gallery-slider').slick({
                dots: true,
                infinite: true,
                speed: 300,
                slidesToShow: 4,
                slidesToScroll: 4,
                responsive: [
                    {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                    },
                    {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                    },
                    {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                    }
                ]
            }); 
            
                document.addEventListener('DOMContentLoaded', function() {
                    
                    const form = document.getElementById('uberForm');
                    const statusMsg = document.getElementById('statusMessage');

                    if (form) {
                    form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const userId = document.body.getAttribute('data-user-id');
                    const isUpdate = userId && userId !== ''; 

                    const method = isUpdate ? 'PUT' : 'POST';
                    const apiPath = isUpdate ? `/fullstack-webproject/api/users/${userId}` : '/fullstack-webproject';

                    const formData = new FormData(form);
                    const bodyData = new URLSearchParams(formData);

                    fetch(apiPath, {
                        method: method,
                        body: bodyData, 
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest' 
                        }
                    })
                    .then(response => {
                        return response.text().then(text => {
                        if (text.trim().startsWith('{')) {
                            return JSON.parse(text);
                       }else {
                        throw new Error('Ошибка сервера: ' + text.substring(0, 50));
                    }
                });
            })
            .then(data => {
                document.querySelectorAll('.error-text').forEach(el => el.remove());

                if (data.status === 'error') {
                    if (statusMsg) {
                        statusMsg.textContent = 'Ошибка.';
                        statusMsg.className = 'status error';
                    }
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const input = document.getElementById(key);
                            if (input) {
                                const errSpan = document.createElement('span');
                                errSpan.className = 'error-text';
                                errSpan.style.color = '#ff4d4d';
                                errSpan.style.display = 'block';
                                errSpan.textContent = data.errors[key];
                                input.closest('.input-group')?.appendChild(errSpan);
                            }
                        });
                    }
                } else if (data.status === 'success') {
                    if (data.mode === 'register') {
                        if (statusMsg) {
                            statusMsg.innerHTML = `
                                <div class="success-alert" style="background: rgba(46, 204, 113, 0.2); padding: 15px; color: #fff;">
                                    <p>Заявка отправлена!</p>
                                    <p>Логин: <code>${data.login}</code></p>
                                    <p>Пароль: <code>${data.password}</code></p>
                                </div>`;
                        }
                        form.reset();
                    } else {
                        if (statusMsg) {
                            statusMsg.innerHTML = '<p style="color: #2ecc71;">Данные обновлены</p>';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (statusMsg) {
                    statusMsg.textContent = 'Ошибка: ' + error.message;
                }
            });
        });
    }




                });
