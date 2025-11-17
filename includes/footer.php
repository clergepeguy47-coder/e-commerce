<!-- Footer -->

<footer class="site-footer">
    <div class="footer-container">
        <!-- Section À propos -->
        <div class="footer-section">
            <h3>🛍 E-Commerce</h3>
            <p>Votre boutique en ligne de confiance pour tous vos achats. Qualité garantie et livraison rapide.</p>
            <div class="social-links">
                <a href="https://www.facebook.com/share/17QbNmysBN/?mibextid=wwXIfr" class="social-icon" title="Facebook" target="_blank">📘</a>
                <a href="https://www.instagram.com/john_peguy47?igsh=Z3UweGsyZnp0a2Ix&utm_source=qr/" class="social-icon" title="Instagram" target="_blank">📷</a>
        </div>
        </div>


    <!-- Section Liens rapides -->
    <div class="footer-section">
        <h3>Liens rapides</h3>
        <ul class="footer-links">
            <li><a href="../index.php">🏠 Accueil</a></li>
            <li><a href="../pages/products.php">🛒 Produits</a></li>
            <li><a href="../pages/categories.php">📂 Catégories</a></li>
            <li><a href="../pages/cart.php">🛒 Mon panier</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../pages/checkout.php">👤 Mon compte</a></li>
            <?php else: ?>
                <li><a href="../pages/login.php">🔐 Connexion</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Section Service client -->
    <div class="footer-section">
        <h3>Service client</h3>
        <ul class="footer-links">
            <li><a href="#aide">❓ Centre d'aide</a></li>
            <li><a href="#contact">📧 Nous contacter</a></li>
            <li><a href="#livraison">🚚 Livraison & Retours</a></li>
            <li><a href="#suivi">📦 Suivre ma commande</a></li>
            <li><a href="#faq">💬 FAQ</a></li>
        </ul>
    </div>

    <!-- Section Informations légales -->
    <div class="footer-section">
        <h3>Informations légales</h3>
        <ul class="footer-links">
            <li><a href="#cgv">📄 CGV</a></li>
            <li><a href="#mentions">⚖ Mentions légales</a></li>
            <li><a href="#confidentialite">🔒 Confidentialité</a></li>
            <li><a href="#cookies">🍪 Cookies</a></li>
        </ul>
    </div>

    <!-- Section Contact -->
    <div class="footer-section">
        <h3>Contactez-nous</h3>
        <ul class="footer-contact">
            <li>📍 Chez moi</li>
            <li>📞 +594 694169793</li>
            <li>📧 clergepeguy47@gmail.com</li>
            <li>🕒 Lun-Sam: 6h-18h</li>
        </ul>
    </div>

    <!-- Section Newsletter -->
    <div class="footer-section newsletter-section">
        <h3>Newsletter</h3>
        <p>Inscrivez-vous pour recevoir nos offres exclusives !</p>
        <form class="newsletter-form" method="POST" action="../functions/newsletter.php">
            <input type="email" name="email" placeholder="Votre email" required>
            <button type="submit">S'inscrire</button>
        </form>
    </div>
</div>

<!-- Méthodes de paiement -->
<div class="payment-methods">
    <div class="payment-container">
        <p>Modes de paiement acceptés:</p>
        <div class="payment-icons">
            <span class="payment-badge">💳 Carte bancaire</span>
            <span class="payment-badge">🏦 Virement</span>
        </div>
    </div>
</div>

<!-- Copyright -->
<div class="footer-bottom">
    <div class="footer-container">
        <p>&copy; <?php echo date('Y'); ?> E-Commerce. Tous droits réservés.</p>
        <p>Développé avec ❤ par <a href="#" style="color: #667eea;">Votre Équipe</a></p>
    </div>
</div>


</footer>

<style>
.site-footer {
    background: linear-gradient(135deg, #e77808ff 0%, #e60d3cff 100%);
    color: white;
    margin-top: 800px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 10px 10px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 100px;
}

.footer-section h3 {
    font-size: 1.3em;
    margin-bottom: 20px;
    color: #643c3cff;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 10px;
}

.footer-section p {
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 15px;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    font-size: 1.2em;
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-icon:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-3px);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.footer-links a:hover {
    color: #fff;
    padding-left: 5px;
}

.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact li {
    margin-bottom: 12px;
    color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 10px;
}

.newsletter-form {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.newsletter-form input {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 5px;
    font-size: 0.95em;
}

.newsletter-form button {
    padding: 12px 25px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.newsletter-form button:hover {
    background: #764ba2;
    transform: translateY(-2px);
}

.payment-methods {
    background: rgba(0, 0, 0, 0.2);
    padding: 30px 20px;
    margin-top: 20px;
}

.payment-container {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

.payment-container p {
    margin-bottom: 15px;
    font-size: 0.95em;
    color: rgba(255, 255, 255, 0.8);
}

.payment-icons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 15px;
}

.payment-badge {
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 0.9em;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.footer-bottom {
    background: rgba(0, 0, 0, 0.3);
    padding: 25px 20px;
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-bottom p {
    margin: 5px 0;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9em;
}

.footer-bottom a {
    color: #667eea;
    text-decoration: none;
    font-weight: 200;
    transition: color 0.3s;
}

.footer-bottom a:hover {
    color: #fff;
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        padding: 40px 20px 20px;
        gap: 30px;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .newsletter-form button {
        width: 100%;
    }

    .payment-icons {
        flex-direction: column;
        align-items: center;
    }

    .payment-badge {
        width: 100%;
        max-width: 300px;
    }
}

@media (max-width: 480px) {
    .social-links {
        justify-content: center;
    }

    .footer-section {
        text-align: center;
    }

    .footer-links a,
    .footer-contact li {
        justify-content: center;
    }
}

/* Animation au scroll */
.site-footer {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- Bouton retour en haut -->

<button id="back-to-top" onclick="scrollToTop()" title="Retour en haut">
    ⬆
</button>

<style>
#back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.5em;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
}

#back-to-top.show {
    opacity: 1;
    visibility: visible;
}

#back-to-top:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}
</style>

<script>
// Afficher/masquer le bouton retour en haut
window.addEventListener('scroll', function() {
    const backToTopBtn = document.getElementById('back-to-top');
    if (window.pageYOffset > 300) {
        backToTopBtn.classList.add('show');
    } else {
        backToTopBtn.classList.remove('show');
    }
});

// Fonction pour remonter en haut
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Validation du formulaire newsletter
document.querySelector('.newsletter-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    if (email) {
        // Simuler l'envoi
        alert('Merci de votre inscription à notre newsletter ! 📧');
        this.reset();
    }
});
<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
</script>
