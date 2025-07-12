<?php
$page_title = 'Homepage';
include 'header.php';
require_once 'includes/session.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
<style>
    .container2 {
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 5rem;
        gap: 15rem;
        flex-wrap: wrap;
    }

    .handbag {
        flex: 1;
        max-width: 300px;
        text-align: center;
        color: #fff;
    }

    .handbag img {
        width: 150%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        transform: translateX(-16%);
    }

    .label {
        font-size: 33px;
        font-weight: bold;
        margin-top: 15px;
    }

    .subtext {
        font-size: 18px;
        margin-top: 5px;
        opacity: 0.8;
    }

    body {
        scroll-behavior: smooth;
        scroll-snap-type: y mandatory;
        overflow-y: scroll;
        margin: 0;
        padding: 0;

    }

    section {
        margin: 0;
        padding: 0;
        height: 100vh;
        max-width: 100%;
        scroll-snap-align: start;
        background-size: cover;
        background-position: center;
        border: none;
        gap: 0;
    }

    .section1 {
        scroll-snap-align: start;
        height: 100vh;
        margin: 0;
        padding: 0;
        border: none;
        background-image: url('images/bg1.png');
        background-size: cover;
        background-position: center;
    }

    .section2 {
        scroll-snap-align: start;
        height: 100vh;
        margin: 0;
        padding: 0;
        border: none;
        background-color: #3f1a41;
    }

    .section3 {
        scroll-snap-align: start;
        height: 70vh;
        margin: 0;
        padding: 0;
        border: none;
        background-color: #231942;
        background-image: url('images/bg3.png');
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }


    .handbag-text {
        position: absolute;
        top: 30%;
        left: 5%;
        background-color: #231942;
        opacity: 0.8;
        color: white;
        text-align: left;
        z-index: 1;
        max-width: 700px;
        padding-left: 1rem;
        margin: 0;
    }

    .handbag-text h1 {
        font-size: 2.5rem;
        font-weight: 500;
        padding-bottom: 0;
        letter-spacing: 1px;
    }

    .handbag-text p {
        font-size: 1.4rem;
        line-height: 1.6;
    }

    .newsletter {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2rem;
        color: rgb(255, 255, 255);
    }

    .newsletter h1 {
        margin-top: 10rem;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.3rem;
    }

    .newsletter p {
        margin-top: 0;
        font-size: 1.2rem;
    }

    /* Responsive adjustments for .container2 and .handbag */
    @media (max-width: 1200px) {
        .container2 {
            gap: 5rem;
            padding-top: 2rem;
        }
    }

    @media (max-width: 900px) {
        .container2 {
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            padding-top: 1.5rem;
        }

        .handbag img {
            width: 100%;
            transform: none;
        }

        .handbag {
            max-width: 90vw;
        }
    }

    @media (max-width: 600px) {
        .handbag {
            max-width: 100vw;
        }

        .label {
            font-size: 1.3em;
        }

        .subtext {
            font-size: 1em;
        }

        .newsletter h1 {
            margin-top: 2rem;
            font-size: 1.5rem;
        }

        .newsletter input[type="email"] {
            width: 90vw !important;
            font-size: 1em;
        }
    }
</style>

<body>
    <div class="section section1">
        <div class="handbag-text">
            <h1>HANDCRAFTED BAYONG BAGS</h1>
            <p>Discover our collection of beautifully crafted Filipino bags that blend tradition with modern style.</p>
        </div>
    </div>
    <div class="section section2">
        <div class="container2">
            <div class="handbag">
                <img src="images/handbag1.png" alt="Handcrafted handbag with sunflowers">
                <div class="label">handcrafted</div>
                <div class="subtext">carefully made by skilled artisans</div>
            </div>
            <div class="handbag">
                <img src="images/handbag2.png" alt="Sustainable handbag with purple flowers">
                <div class="label">sustainable</div>
                <div class="subtext">eco-friendly materials and practices</div>
            </div>
            <div class="handbag">
                <img src="images/handbag3.png" alt="Unique handbag with yellow flowers">
                <div class="label">unique</div>
                <div class="subtext">one-of-a-kind designs for every style</div>
            </div>
        </div>

    </div>
    <div class="section section3">
        <div class="newsletter">
            <h1>STAY UPDATED</h1>
            <p>subscribe to our newsletter for exclusive offers and updates</p>

            <form action="subscribe_newsletter.php" method="post" style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                <input type="email" name="email" placeholder="Enter your email address" required style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc; opacity: 0.8;">
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
</body>

</html>

</div>

<?php include 'footer.php'; ?>

<script>
    <?php
    if (isset($_SESSION['alert'])) {
        $alert_type = $_SESSION['alert']['type'];
        $alert_message = $_SESSION['alert']['message'];
        echo "showToast('" . addslashes($alert_message) . "', '" . ($alert_type === 'success' ? 'success' : 'error') . "');";
        unset($_SESSION['alert']);
    }


    ?>


    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }, 100);
    }
</script>