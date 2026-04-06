<?php
    session_start();
    date_default_timezone_set('America/Lima');

    $config = parse_ini_file("/var/www/resources/php/hubtec-io/.env", true);
    extract($config);

    require "/var/www/resources/php/hubtec-io/vars.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php echo ($MASTER_ENVIRONMENT == "prod" ? $head_gtag : null) ?>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HubTec - Innovation at the Core</title>
        <meta name="description" content="">
        <meta name="keywords" content="">

        <!-- Favicons -->
        <link href="assets/img/favicon.png" rel="icon">
        <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com" rel="preconnect">
        <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

        <!-- Vendor CSS Files -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9xmBN9iUUhIndKtxwILGWYOSibOKjLsEdjyjZvYDq/cZwNeak0w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.css" integrity="sha512-rd0qOHVMOcez6pLWPVFIv7EfSdGKLt+eafXh4RO/12Fgr41hDQxfGvoi1Vy55QIVcQEujUE1LQrATCLl2Fs+ag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link href="assets/css/main.css" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/axios@1.13.6/dist/axios.min.js"></script>
    </head>

    <body class="index-page">
        <?php echo ($MASTER_ENVIRONMENT == "prod" ? $body_gtag : null) ?>

        <header id="header" class="header d-flex align-items-center sticky-top">
            <div class="container-fluid container-xl position-relative d-flex align-items-center">
                <a href="index.html" class="logo d-flex align-items-center me-auto">
                    <img src="assets/img/HubTec-LogoWEB.png" alt="">
                </a>

                <nav id="navmenu" class="navmenu">
                    <ul>
                        <li><a href="#hero" class="active">Inicio</a></li>
                        <li><a href="#about">Nosotros</a></li>
                        <!--
                        <li><a href="#services">Servicios</a></li>
                        -->
                        <li><a href="#clientes">Clientes</a></li>
                        <!--
                        <li><a href="#team">Nuestro Equipo</a></li>
                        -->
                        <!--
                        <li class="dropdown"><a href="#"><span>Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="#">Dropdown 1</a></li>
                            <li class="dropdown"><a href="#"><span>Deep Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                            <ul>
                                <li><a href="#">Deep Dropdown 1</a></li>
                                <li><a href="#">Deep Dropdown 2</a></li>
                                <li><a href="#">Deep Dropdown 3</a></li>
                                <li><a href="#">Deep Dropdown 4</a></li>
                                <li><a href="#">Deep Dropdown 5</a></li>
                            </ul>
                            </li>
                            <li><a href="#">Dropdown 2</a></li>
                            <li><a href="#">Dropdown 3</a></li>
                            <li><a href="#">Dropdown 4</a></li>
                        </ul>
                        </li>
                        <li><a href="#contact">Contact</a></li>
                        -->
                    </ul>
                    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
                </nav>
                <a class="btn-getstarted" href="intranet/login">Área de clientes</a>
            </div>
        </header>

        <main class="main">
            <!-- Begin Hero Section -->
            <section id="hero" class="hero section">
                <div class="container">
                    <div class="row gy-4">
                        <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="fade-up">
                            <h1 id="hero_title"></h1>
                            <p id="hero_sub_title"></p>
                            <div class="d-flex">
                                <!-- <a href="#about" class="btn-get-started">Conócenos</a>  -->
                                <!--<a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox btn-watch-video d-flex align-items-center"><i class="bi bi-play-circle"></i><span>Watch Video</span></a>-->
                            </div>
                        </div>
                        <div class="col-lg-6 order-1 order-lg-2 hero-img justify-content-top" data-aos="zoom-out" data-aos-delay="100">
                            <img src="assets/img/HubTec_LOGO.png" class="img-fluid animated" alt="">
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Hero Section -->

            <center>
                <section id="carrousel" class="clientes section">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/IntraPeru.png" alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/EntreJuegosySonrisas.png" alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/Fusiontel.png" alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/OzoneMusicStore.png " alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/Shili.png " alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/Biomedist.png" alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/Ronronnerie.png" alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/ROchoa.png" alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/BigNetwork.png" alt="">
                            </div>
                            <div class="swiper-slide">
                                <img src="assets/img/clientes/DVogue.png" alt="">
                            </div>
                        </div>
                    </div>
                </section>
            </center>

            <!-- Begin About Section -->
            <section id="about" class="about section">
                <!-- Section Title -->
                <div class="container section-title" data-aos="fade-up">
                    <span id="about_us_title_shadow"><br></span>
                    <h2 id="about_us_title"></h2>
                    <p id="about_us_description"></p>
                </div>
                <!-- End Section Title -->

                <div class="container" align="center">
                    <img src="assets/img/enConstruccion.png" height="50%" width="50%" alt="">
                </div>
            </section>
            <!-- End About Section -->

            <!-- Begin Clients -->
            <section id="clientes" class="portfolio section">
                <!-- Section Title -->
                <div class="container section-title" data-aos="fade-up">
                    <span id="clients_title_shadow"></span>
                    <h2 id="clients_title"></h2>
                    <p id="clients_description"></p>
                </div>
                <!-- End Section Title -->

                <center>
                    <section id="carrousel" class="clientes section">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/IntraPeru.png" alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/EntreJuegosySonrisas.png" alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/Fusiontel.png" alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/OzoneMusicStore.png " alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/Shili.png " alt="">
                                </div>
                                    <div class="swiper-slide">
                                    <img src="assets/img/clientes/Biomedist.png" alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/Ronronnerie.png" alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/ROchoa.png" alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/BigNetwork.png" alt="">
                                </div>
                                <div class="swiper-slide">
                                    <img src="assets/img/clientes/DVogue.png" alt="">
                                </div>
                            </div>
                        </div>
                    </section>
                </center>
            </section>
            <!-- End Clients Section -->
        </main>

        <footer id="footer" class="footer">
            <div class="container copyright text-center mt-4">
                <p id="copyright"></p>
            </div>
        </footer>

        <!-- Scroll Top -->
        <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

        <!-- Preloader -->
        <div id="preloader"></div>

        <!-- Vendor JS Files -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" integrity="sha512-A7AYk1fGKX6S2SsHywmPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js" integrity="sha512-Ysw1DcK1P+uYLqprEAzNQJP+J4hTx4t/3X2nbVwszao8wD+9afLjBQYjz7Uk4ADP+Er++mJoScI42ueGtQOzEA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Main JS File -->
        <script src="assets/js/main.js"></script>
        <script src="assets/js/hubtec/homepage.js"></script>
    </body>

    <script source="text/Javascript">
        var miSwiper = new Swiper('.swiper-container', {
            touchMoveStopPropagation: false,
            variableWidth: true,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 800,
                disableOnInteraction: false,
            },
            breakpoints: {
                // when window width is >= 320px
                320: {
                slidesPerView: 1,
                spaceBetween: 50
                },
                // when window width is >= 480px
                480: {
                slidesPerView: 1,
                spaceBetween: 50
                },
                // when window width is >= 640px
                640: {
                slidesPerView: 4,
                spaceBetween: 65
                }
            }
        });
    </script>
</html>
