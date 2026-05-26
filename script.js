$('.gallery-slider').slick({
    dots: true,
    infinite: true,
    speed: 300,
    slidesToShow: 4,
    slidesToScroll: 4,
    responsive: [
        { breakpoint: 1024, settings: { slidesToShow: 3, slidesToScroll: 3 } },
        { breakpoint: 600, settings: { slidesToShow: 2, slidesToScroll: 2 } },
        { breakpoint: 480, settings: { slidesToShow: 1, slidesToScroll: 1 } }
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
            const apiPath = isUpdate ? `/fullstack-webproject/api/users/${userId}` : '/fullstack-webproject/api/users';

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
                    } else {
                        throw new Error('Ошибка сервера: ' + text.substring(0, 50));
                    }
                });
            })
            .then(data => {
                // Удаляем старые ошибки
                document.querySelectorAll('.error-text').forEach(el => el.remove());

                if (data.status === 'error') {
                    if (statusMsg) {
                        statusMsg.innerHTML = '<div class="error" style="color: #ff4d4d; padding: 10px; background: rgba(255, 77, 77, 0.1); border-radius: 5px; margin-bottom: 15px;">Пожалуйста, исправьте ошибки в форме</div>';
                        statusMsg.className = 'status error';
                    }
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            // Ищем поле по name (не по id)
                            let input = document.querySelector(`[name="${key}"]`);
                            if (!input && key === 'telephone') input = document.querySelector(`[name="telephone"]`);
                            if (!input && key === 'mail') input = document.querySelector(`[name="mail"]`);
                            if (!input && key === 'firstName') input = document.querySelector(`[name="firstName"]`);
                            if (!input && key === 'roomType') input = document.querySelector(`[name="roomType"]`);
                            if (!input && key === 'agreement') input = document.querySelector(`[name="agreement"]`);
                            
                            if (input) {
                                const errSpan = document.createElement('span');
                                errSpan.className = 'error-text';
                                errSpan.style.color = '#ff4d4d';
                                errSpan.style.display = 'block';
                                errSpan.style.fontSize = '12px';
                                errSpan.style.marginTop = '5px';
                                errSpan.textContent = data.errors[key];
                                input.parentNode.insertBefore(errSpan, input.nextSibling);
                            } else {
                                // Если поле не найдено, показываем ошибку в statusMsg
                                if (statusMsg) {
                                    const errSpan = document.createElement('p');
                                  errSpan.style.color = '#ff4d4d';
                                    errSpan.textContent = data.errors[key];
                                    statusMsg.appendChild(errSpan);
                                }
                            }
                        });
                    }
                } else if (data.status === 'success') {
                    if (data.mode === 'register') {
                        if (statusMsg) {
                            statusMsg.innerHTML = `
                                <div class="success-alert" style="background: rgba(46, 204, 113, 0.2); padding: 15px; border-radius: 5px; color: #fff; margin-bottom: 15px;">
                                    <p style="margin: 0 0 10px 0;">✅ Заявка отправлена!</p>
                                    <p style="margin: 5px 0;">Логин: <code style="background: #2ecc71; padding: 2px 6px; border-radius: 4px;">${data.login}</code></p>
                                    <p style="margin: 5px 0;">Пароль: <code style="background: #2ecc71; padding: 2px 6px; border-radius: 4px;">${data.password}</code></p>
                                </div>
                            `;
                        }
                        form.reset();
                    } else {
                        if (statusMsg) {
                            statusMsg.innerHTML = '<div class="success-alert" style="background: rgba(46, 204, 113, 0.2); padding: 15px; border-radius: 5px; color: #fff;">✅ Данные обновлены</div>';
                        }
                    }
                    // Скрываем сообщение через 5 секунд
                    setTimeout(() => {
                        if (statusMsg) statusMsg.innerHTML = '';
                    }, 30000);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (statusMsg) {
                    statusMsg.innerHTML = '<div class="error" style="color: #ff4d4d; padding: 10px; background: rgba(255, 77, 77, 0.1); border-radius: 5px;">❌ Ошибка соединения с сервером</div>';
                }
            });
        });
    }
});
