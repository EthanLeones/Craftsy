<?php $page_title = 'Privacy Policy'; ?>
<?php include 'header.php'; ?>

<style>
.policy-page-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 80px 40px;
    background: #ffffff;
    min-height: 80vh;
}

.policy-page-title {
    font-size: 2rem;
    color: #3f1a41;
    text-align: center;
    margin-bottom: 80px;
    font-weight: 200;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.policy-section {
    margin-bottom: 50px;
    padding-bottom: 30px;
    border-bottom: 1px solid #f0f0f0;
}

.policy-section:last-child {
    border-bottom: none;
}

.policy-section h2 {
    font-size: 1.4rem;
    color: #3f1a41;
    margin-bottom: 25px;
    font-weight: 300;
    letter-spacing: 2px;
}

.policy-section p {
    color: #666666;
    font-size: 1rem;
    line-height: 1.7;
    font-weight: 300;
    margin: 0;
}

@media (max-width: 768px) {
    .policy-page-wrapper {
        padding: 60px 20px;
    }
    
    .policy-page-title {
        font-size: 1.6rem;
        letter-spacing: 3px;
        margin-bottom: 60px;
    }
    
    .policy-section h2 {
        font-size: 1.2rem;
        letter-spacing: 1px;
    }
}
</style>

<div class="policy-page-wrapper">
    <h1 class="policy-page-title">Privacy Policy</h1>

    <section class="policy-section">
        <h2>Information We Collect</h2>
        <p>We collect personal information that you voluntarily provide to us when you register on the site, place an order, subscribe to our newsletter, or fill out a form. This information may include your name, email address, mailing address, phone number, and payment information.</p>
    </section>

    <section class="policy-section">
        <h2>How We Use Your Information</h2>
        <p>We may use the information we collect from you to process your transactions, send periodic emails regarding your order or other products and services, personalize your experience, improve our website, and provide customer service.</p>
    </section>

    <section class="policy-section">
        <h2>Protection of Your Information</h2>
        <p>We implement a variety of security measures to maintain the safety of your personal information when you place an order or enter, submit, or access your personal information.</p>
    </section>

    <section class="policy-section">
        <h2>Disclosure to Third Parties</h2>
        <p>We do not sell, trade, or otherwise transfer to outside parties your personally identifiable information without your consent, other than for the express purpose of delivering the purchased product or service.</p>
    </section>
</div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?> 