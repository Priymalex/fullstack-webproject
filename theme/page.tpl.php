<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aveji</title>
        <link rel="stylesheet" href="styles/style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    </head>
    <body data-user-id="<?= $_SESSION['user_id'] ?? '' ?>">
        <header>
            <img src="img/logo.svg" alt="logo">
            <div class = "header-text">
                <nav>
                        <p><a href = "#hero">О нас</a></p>
                        <p><a href = "#projects">Проекты</a></p>
                        <p><a href = "#about">Материалы</a></p>
                        <p><a href = "#reviews">Отзывы</a></p>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                        <p><a href="#reviews"><?php echo htmlspecialchars($_SESSION['login'] ?? 'Мой профиль'); ?></a></p>
                        <p><a href="/fullstack-webproject/modules/logout.php" style="color: #ff4d4d;">Выйти</a></p>
                    <?php else: ?>
                        <p><a href="/fullstack-webproject/modules/login.php">Войти</a></p>
                    <?php endif; ?>
                </nav>
                <p><a type="tel:+7 999 999 999">+7 999 999 999</a></p>
            </div>
            <div class="hamburger-menu">
                    <input id="menu__toggle" type="checkbox" />
                    <label class="menu__btn" for="menu__toggle"><span></span></label>
                    <ul class="menu__box">
                        <li><a class="menu__item" href="#hero">О нас</a></li>
                        <li><a class="menu__item" href="#projects">Проекты</a></li>
                        <li><a class="menu__item" href="#about">Материалы</a></li>
                        <li><a class="menu__item" href="#reviews">Отзывы</a></li>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                        <li><a  class="menu__item" href="#reviews"><?php echo htmlspecialchars($_SESSION['login'] ?? 'Мой профиль'); ?></a></li>
                        <li><a  class="menu__item" href="/fullstack-webproject/modules/logout.php" style="color: #ff4d4d;">Выйти</a></li>
                    <?php else: ?>
                        <li><a  class="menu__item" href="/fullstack-webproject/modules/login.php">Войти</a></li>
                    <?php endif; ?>
                    </ul>
            </div>
        </header>
        <main>
            <div class = "hero" id = "hero">
                <div class = "hero-header">
                    <div class="hero-header-text">
                        <h1>Эксклюзивная<br> и нестандартная<br> мебель для дома</h1>
                    </div>
                    <a href = "#form" ><p style="text-decoration: underline; font-size: 18px; text-transform: uppercase;">заказать проект</p></a>
                </div>
                <div class = "hero-content">
                    <div class = "hero-content-left">
                            <p style= "font-size: 18px;  line-height: 1.6;">Мы можем произвести любую мебель для вашего проекта и найти производственное решение любой задумки.</p>
                            <img src = "img/michael-oxendine-GHCVUtBECuY-unsplash (1) 1.png" alt = "" style="width: 80%; height: 50%;">
                    </div>
                    <div class = "hero-content-right">
                        <img src="img/spacejoy-IH7wPsjwomc-unsplash (1) 1.png" alt="" style="width: 100%; height: 100%;">
                    </div>
                </div>
            </div>
            <div class = "about" id = "about">
                <div class="about-left">
                    <h2><span class="desktop-about">Более 5 лет создаем мебель для вашего комфорта</span><span class="mobile-about">О компании</span></h2>
                </div>
                <div class="about-right">
                    <div class = "about-right-uppertext">
                        <p class="about-right-uppertext-text">Мы — команда профессионалов, которые могут произвести любую мебель для вашего проекта, а также найти производственное решение любой задумки.</p>
                        <p class="about-right-uppertext-text">Наша основная цель — реализовывать самые смелые задумки, и делать это качественно и аккуратно.</p>
                        <p class="about-right-uppertext-text">В качестве материалов мы используем натуральные — стекло, дерево, бетон, камень, металл и эпоксидную смолу.</p>
                    </div>
                    <div class = "about-right-lowertext">
                        <h3>1 год</h3>
                        <p>гарантии на всю<br>продукцию</p>
                        <h3>300+</h3>
                        <p>выполненных<br>проектов</p>
                        <h3>15 дней</h3>
                        <p>срок производства</p>
                    </div>
                </div>
            </div>
            <div class = "steps">
                <div class = "steps-left">
                    <h2>Как мы работаем</h2>
                </div>
                <div class = "steps-right">
                    <div class = "steps-right-header">
                        <p>Алгоритм работы с нами для удобства и понимания проекта</p>
                    </div>
                    <div class = "steps-right-content">
                        <div class = "steps-right-content-part" style="margin-top: 2vh;">
                            <h3 class = "steps-h">Идея</h3>
                            <p class = "steps-text">Клиент приходит к нам с идеей. Это может быть изображение: эскиз или другой референс. А мы думаем над тем, как реализовать данные идеи, делаем технические чертежи и предлагаем решения по материалам.</p>
                        </div>
                        <div class = "steps-right-content-part">
                            <h3 class = "steps-h">Техническое задание</h3>
                            <p class = "steps-text">Вместе с клиентом формулируем корректное ТЗ, которое включает в себя визуализацию изделия, эскизный чертёж с габаритами, информацию по материалам и отделке, срок реализации проекта и другие обязательные пункты.</p>
                        </div>
                        <div class = "steps-right-content-part">
                            <h3 class = "steps-h">Коммерческое предложение</h3>
                            <p class = "steps-text">Предпочтительно используем натуральные материалы. Но любую смету можем оптимизировать, упростив материалы или конструктив. Сможем подстроиться под бюджет клиента и согласуем коммерческое предложение.</p>
                        </div>
                        <div class = "steps-right-content-part">
                            <h3 class = "steps-h">Подготовка рабочего проекта</h3>
                            <p class = "steps-text">Создадим рабочую документацию и чертежи. Это фундамент качественного производства. На этом этапе утверждаем с клиентом все габариты и материалы, чертежи и приступаем к производству.</p>
                        </div>
                        <div class = "steps-right-content-part">
                            <h3 class = "steps-h">Производство и монтаж</h3>
                            <p class = "steps-text">Производство занимает от 15 рабочих дней, в зависимости от сложности и объёма. Монтажом тоже занимаемся самостоятельно. Ведь мы это сделаем быстро и аккуратно.</p>
                        </div>
                    </div>
                </div>
            </div>
            <h2>Проекты</h2>
            <div class="container" id = "projects">
                <div class = "gallery-container">
                    <div class = "gallery-slider">
                    <img class="slide" src="img/project (1).png" alt="">
                    <img class="slide" src="img/project (2).png" alt="">
                    <img class="slide" src="img/project (3).png" alt="">
                    <img class="slide" src="img/project (4).png" alt="">
                    <img class="slide" src="img/project-img.png" alt="">
                    <img class="slide" src="img/project.png" alt="">
                </div>
            </div>
            </div>
            <h2>Отзывы</h2>
            <div class = "reviews" id = "reviews">
                    <div class = "review">
                        <img class="review-icon" src = "img/icon.png" alt="icon">
                        <div class= "review-text">
                            <p class = "text-large-web">Игорь Антонов</p>
                            <p>Обратилась к Aveji по рекомендации. Команда сразу поняла, какой дизайн я хочу. Предоставили несколько вариантов и в течение недели сделали наброски. Итог понравился, все на высшем уровне.</p>
                        </div>
                    </div>
                    <div class = "review">
                        <img class="review-icon" src = "img/icon.png" alt="icon">
                        <div class= "review-text">
                            <p class = "text-large-web">Ольга Иванова</p>
                            <p>После пары заказов у компании Aveji убедилась, что за мебелью теперь только к ним. Абсолютно любые решения, в любых размерах и форм-факторе, то что нужно!</p>
                        </div>
                    </div>
                    <div class = "review">
                        <img class="review-icon" src = "img/icon.png " alt="icon">
                        <div class= "review-text">
                            <p class = "text-large-web">Аркадий Макаров</p>
                            <p>Aveji — настоящие профессионалы своего дела. Быстро поняли мою задумку, сделали дизайн, согласовали и изготовили мебель. А потом еще и бесплатно все собрали на месте. Большое спасибо!</p>
                        </div>
                    </div>
            </div>
            <div class= "form" id = "form">
                <div class = "img-form"><img src = "img/bilal-mansuri-yJ78NE83YH8-unsplash 1.png" alt = "img-form"></div>
                    <div class = "form-form">
                    <h2>Хотите заказать проект?</h2>
                    <p>Оставьте заявку, и мы вам перезвоним</p>
                    <form action = "form-fallback" method="POST" id="uberForm">

                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="_gotcha">

                         <div id="statusMessage" class="status">
                        <?php if ($c['success']): ?>
                            <div class="success-alert">
                                <p style="color: #2ecc71; font-weight: bold; margin-bottom: 10px;">Заявка успешно отправлена!</p>
                                <?php if ($c['gen_login'] && $c['gen_pass']): ?>
                                    <p>Для вас автоматически создан аккаунт:</p>
                                    <p>Логин: <code><?php echo htmlspecialchars($c['gen_login']); ?></code></p>
                                    <p>Пароль: <code><?php echo htmlspecialchars($c['gen_pass']); ?></code></p>
                                <?php else: ?>
                                    <p>Данные вашего профиля успешно обновлены.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                        <input type="text" name = "firstName" class = "formInput" placeholder="Имя " required value="<?php echo htmlspecialchars($c['values']['firstName'] ?? ''); ?>">
                        <?php if (!empty($c['errors']['firstName'])): ?>
                            <span class="error-text"><?php echo $c['errors']['firstName']; ?></span>
                        <?php endif; ?>
                        <input type="email" name = "mail" class = "formInput" placeholder="E-mail " value="<?php echo htmlspecialchars($c['values']['mail'] ?? ''); ?>">
                        <?php if (!empty($c['errors']['email'])): ?>
                            <span class="error-text"><?php echo $c['errors']['email']; ?></span>
                        <?php endif; ?>
                        <input type="tel" name="telephone" class = "formInput" placeholder="Телефон " value="<?php echo htmlspecialchars($c['values']['phone'] ?? ''); ?>">
                        <?php if (!empty($c['errors']['phone'])): ?>
                            <span class="error-text"><?php echo $c['errors']['phone']; ?></span>
                        <?php endif; ?>
                        <div class="form-group">
                <label for="roomType">Тип помещения (можно выбрать несколько):</label>
                <select name="roomType[]" id="roomType" class="formInput form-select" multiple size="3">
                    <option value="Гостиная" <?= in_array('Гостиная', (array)($c['values']['rooms'] ?? [])) ? 'selected' : '' ?>>Гостиная</option>
                    <option value="Спальня" <?= in_array('Спальня', (array)($c['values']['rooms'] ?? [])) ? 'selected' : '' ?>>Спальня</option>
                    <option value="Кухня" <?= in_array('Кухня',(array)($c['values']['rooms'] ?? [])) ? 'selected' : '' ?>>Кухня</option>
                    <option value="Кабинет" <?= in_array('Кабинет', (array)($c['values']['rooms'] ?? [])) ? 'selected' : '' ?>>Кабинет</option>
                    <option value="Ванная" <?= in_array('Ванная', (array)($c['values']['rooms'] ?? [])) ? 'selected' : '' ?>>Ванная комната</option>
                    <option value="Прихожая" <?= in_array('Прихожая', (array)($c['values']['rooms'] ?? [])) ? 'selected' : '' ?>>Прихожая</option>
                </select>
                
            </div>

            <!-- Чекбокс согласия с политикой -->
            <div class="form-group checkbox-agreement">
                <label class="checkbox-label">
                    <input type="checkbox" name="agreement" required>
                    Я соглашаюсь с политикой обработки персональных данных</a>
                </label>
                <?php if (!empty($c['errors']['agreement'])): ?>
                            <span class="error-text"><?php echo $c['errors']['consent']; ?></span>
                        <?php endif; ?>
            </div>
                        <button type="submit" name = "sendButton">Отправить заявку</button>
                    </form>
                    <div class="apps">
                        <img src="img/App Store 1.png" alt="app1">
                        <img src="img/Google Play 1.png" alt="app2">
                    </div>
                    </div>
            </div>
        </main>
        <footer>
            <div class = "footer-social">
               <a href=""><p>Вконтакте</p></a>
                <a href=""><p>Телеграм</p></a>
            </div>
            <p id = "policy">Политика конфеденциальности</p>
            <p id = "payment">Оплата и доставка</p>
            <p id = "desktop-part-footer">Политика конфеденциальности / Оплата и доставка</p>
            <p>© 2023 Aveji<br>Все права защищены</p>
        </footer>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
        <script src="script.js"></script>
    </body>
</html>
