<?php $page_title = 'FAQ'; ?>
<?php include 'header.php'; ?>

<style>
    .faq-page-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding: 80px 40px;
        background: #ffffff;
        min-height: 80vh;
    }

    .faq-page-title {
        font-size: 2rem;
        color: #3f1a41;
        text-align: center;
        margin-bottom: 80px;
        font-weight: 200;
        letter-spacing: 4px;
        text-transform: uppercase;
    }

    .faq-item {
        margin-bottom: 40px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 30px;
    }

    .faq-item:last-child {
        border-bottom: none;
    }

    .faq-item h3 {
        font-size: 1.2rem;
        color: #3f1a41;
        margin-bottom: 20px;
        font-weight: 400;
        letter-spacing: 1px;
    }

    .faq-item .answer p {
        color: #666666;
        font-size: 1rem;
        line-height: 1.6;
        font-weight: 300;
        margin: 0;
    }

    @media (max-width: 768px) {
        .faq-page-wrapper {
            padding: 60px 20px;
        }

        .faq-page-title {
            font-size: 1.6rem;
            letter-spacing: 3px;
            margin-bottom: 60px;
        }

        .faq-item h3 {
            font-size: 1.1rem;
        }
    }
</style>

<div class="faq-page-wrapper">
    <h1 class="faq-page-title">Frequently Asked Questions</h1>

    <div class="faq-item">
        <h3>What are bayong bags made of?</h3>
        <div class="answer">
            <p>Our bayong bags are handcrafted using traditional materials like plastic strips, pandan leaves, and buri, offering both durability and style.</p>
        </div>
    </div>

    <div class="faq-item">
        <h3>How do I place an order?</h3>
        <div class="answer">
            <p>Browse our Shop page, select your desired item, choose a size, and click "Add to Cart". Then proceed to Checkout to finalize your order.</p>
        </div>
    </div>

    <div class="faq-item">
        <h3>Do I need an account to order?</h3>
        <div class="answer">
            <p>Yes, creating an account helps us process your order, manage your cart, and track your purchases efficiently.</p>
        </div>
    </div>

    <div class="faq-item">
        <h3>What payment methods are accepted?</h3>
        <div class="answer">
            <p>We currently accept cash on delivery, pickup payments, and digital payments via GCash or bank transfer. Credit card integration is in progress.</p>
        </div>
    </div>

    <div class="faq-item">
        <h3>Where do you deliver?</h3>
        <div class="answer">
            <p>We deliver within Metro Cebu via Maxim delivery. You can also opt for pickup.</p>
        </div>
    </div>
</div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>