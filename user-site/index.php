<?php
session_start();
require_once __DIR__ . '/../shared-core/config.php';
require_once __DIR__ . '/../shared-core/includes/functions.php';

$products = fetchProducts(9);
$featuredProducts = $products;
shuffle($featuredProducts);
$featuredProducts = array_slice($featuredProducts, 0, 3);
$isLoggedIn = isUserLoggedIn();
$userName = getLoggedInUserName();
$redirectUrl = $_SERVER['REQUEST_URI'] ?? '/index.php';
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = 'index.php';
$cartUrl = 'pages/cart.php';
$adminUrl = '/ecommerce-projectphp/admin-site/index.php';
$loginUrl = 'pages/login.php?redirect=' . urlencode($redirectUrl);
$logoutUrl = 'pages/logout.php';
$showHero = true;
$heroTitle = 'Nature in Every Drop';
$heroSubtitle = '"Pure herbal care, thoughtfully chosen for your daily wellness."';
$heroPrimaryUrl = 'pages/products.php';
$heroPrimaryLabel = 'Browse Products';
$heroSecondaryUrl = $cartUrl;
$heroSecondaryLabel = 'View Cart';
$showSamples = false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenStore</title>
    <link rel="icon" href="/ecommerce-projectphp/shared-core/assets/titlelog.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/ecommerce-projectphp/shared-core/assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .reveal-up {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .7s ease, transform .7s ease;
        }

        .reveal-up.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .showcase-card {
            overflow: hidden;
        }

        .showcase-card img,
        .concern-card img {
            transition: transform .45s ease;
        }

        .showcase-card:hover img,
        .concern-card:hover img {
            transform: scale(1.06);
        }

        .section-shell {
            border: 1px solid #e5e7eb;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
            padding: 1.25rem;
        }

        .soft-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .7rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 600;
            color: #166534;
            background: #dcfce7;
        }

        .showcase-card {
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .showcase-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(22, 163, 74, 0.15);
        }

        .concern-card {
            overflow: hidden;
            position: relative;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
        }

        .concern-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0) 20%, rgba(0, 0, 0, .58) 100%);
            opacity: 0.9;
            pointer-events: none;
        }

        .concern-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.14);
            border-color: #86efac;
        }

        .concern-card span {
            position: absolute;
            left: .75rem;
            right: .75rem;
            bottom: .7rem;
            z-index: 1;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
        }

        .trust-strip {
            border: 1px solid #bbf7d0;
            border-radius: 1rem;
            background: linear-gradient(90deg, #14532d 0%, #166534 45%, #15803d 100%);
            color: #f0fdf4;
        }

        .trust-pill {
            display: flex;
            align-items: center;
            gap: .65rem;
            border-radius: .8rem;
            background: rgba(255, 255, 255, .08);
            padding: .75rem .9rem;
        }

        .trust-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, .18);
            font-size: .85rem;
        }

        .featured-shell {
            border: 1px solid #dcfce7;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 45%);
            box-shadow: 0 10px 28px rgba(22, 163, 74, .08);
            padding: 1.25rem;
        }

        .featured-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #ffffff;
            overflow: hidden;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
        }

        .featured-card:hover {
            transform: translateY(-6px);
            border-color: #86efac;
            box-shadow: 0 16px 34px rgba(22, 163, 74, .14);
        }

        .featured-image {
            overflow: hidden;
            border-radius: .8rem;
        }

        .featured-image img {
            width: 100%;
            height: 13rem;
            object-fit: cover;
            transition: transform .45s ease;
        }

        .featured-card:hover .featured-image img {
            transform: scale(1.06);
        }

        .featured-description {
            min-height: 2.7rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../shared-core/includes/layout/header.php'; ?>
    <main class="container mx-auto px-4 py-10">
        <section class="featured-shell reveal-up">
            <div class="flex flex-col gap-2 mb-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="soft-badge">Top Picks</span>
                    <h2 class="mt-2 text-2xl font-semibold">Featured Products</h2>
                </div>
                <span class="text-sm text-gray-500">Handpicked herbal essentials for your routine</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                <?php if (empty($featuredProducts)): ?>
                    <p class="text-gray-600">No products available yet.</p>
                <?php else: ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <?php
                        $descriptionText = trim((string) ($product['description'] ?? ''));
                        $descriptionWords = preg_split('/\s+/', $descriptionText, -1, PREG_SPLIT_NO_EMPTY);
                        $descriptionPreview = $descriptionText;
                        if (is_array($descriptionWords) && count($descriptionWords) > 20) {
                            $descriptionPreview = implode(' ', array_slice($descriptionWords, 0, 20)) . '...';
                        }
                        ?>
                        <div class="featured-card p-4">
                            <div class="featured-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="h-52 w-full rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">Product</div>
                                <?php endif; ?>
                            </div>
                            <h3 class="font-bold mt-4 text-lg"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="featured-description mt-2 text-sm text-gray-600"><?php echo htmlspecialchars($descriptionPreview); ?></p>
                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-lg font-semibold text-green-600">LKR <?php echo number_format($product['price'], 2); ?></p>
                                <span class="text-xs text-gray-500">In stock</span>
                            </div>
                            <?php if ($showSamples): ?>
                                <form action="cart.php" method="post" class="mt-4">
                                    <input type="hidden" name="action" value="add_sample">
                                    <input type="hidden" name="sample_id" value="<?php echo htmlspecialchars($product['sample_id']); ?>">
                                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                    <input type="hidden" name="price" value="<?php echo htmlspecialchars($product['price']); ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <?php if ($isLoggedIn): ?>
                                        <button type="submit" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300">Add Sample to Cart</button>
                                    <?php else: ?>
                                        <button type="button" data-login-required="true" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300">Add Sample to Cart</button>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="text-center bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-100">View Product</a>
                                    <form action="pages/cart.php" method="post" class="w-full">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <?php if ($isLoggedIn): ?>
                                            <button type="submit" class="w-full bg-black text-white py-2 px-4 rounded-lg hover:bg-gray-800">Add to Cart</button>
                                        <?php else: ?>
                                            <button type="button" data-login-required="true" class="w-full bg-black text-white py-2 px-4 rounded-lg hover:bg-gray-800">Add to Cart</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="mt-14 reveal-up section-shell">
            <div class="flex flex-col gap-2 mb-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="soft-badge">Herbal Promise</span>
                    <h2 class="mt-2 text-2xl font-semibold">Why Choose Herbal Care</h2>
                </div>
                <span class="text-sm text-gray-500">Natural. Gentle. Effective.</span>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="showcase-card reveal-up rounded-2xl border border-green-100 bg-green-50 p-5" style="transition-delay:.08s;">
                    <img src="https://images.unsplash.com/photo-1457530378978-8bac673b8062?auto=format&fit=crop&w=900&q=80" alt="Fresh herbal leaves" class="h-40 w-full rounded-xl object-cover">
                    <h3 class="mt-3 text-lg font-semibold text-green-700">100% Natural Ingredients</h3>
                    <p class="mt-2 text-sm text-gray-700">Made with plant-based extracts selected for purity and quality.</p>
                </div>
                <div class="showcase-card reveal-up rounded-2xl border border-green-100 bg-green-50 p-5" style="transition-delay:.16s;">
                    <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=900&q=80" alt="Herbal oil dropper bottle" class="h-40 w-full rounded-xl object-cover">
                    <h3 class="mt-3 text-lg font-semibold text-green-700">No Harsh Chemicals</h3>
                    <p class="mt-2 text-sm text-gray-700">Free from aggressive additives, so your routine stays clean and gentle.</p>
                </div>
                <div class="showcase-card reveal-up rounded-2xl border border-green-100 bg-green-50 p-5" style="transition-delay:.24s;">
                    <img src="https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=900&q=80" alt="Natural daily herbal wellness routine" class="h-40 w-full rounded-xl object-cover">
                    <h3 class="mt-3 text-lg font-semibold text-green-700">Safe for Daily Use</h3>
                    <p class="mt-2 text-sm text-gray-700">Balanced formulations crafted for regular wellness support.</p>
                </div>
            </div>
        </section>

        <section class="mt-14 reveal-up section-shell">
            <div class="flex flex-col gap-2 mb-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="soft-badge">Quick Discovery</span>
                    <h2 class="mt-2 text-2xl font-semibold">Shop by Concern</h2>
                </div>
                <span class="text-sm text-gray-500">Find what fits your need</span>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                <a href="pages/products.php?q=skin" class="concern-card reveal-up rounded-xl border border-gray-200 bg-white p-2 text-center font-medium hover:border-green-300" style="transition-delay:.08s;">
                    <img src="https://images.unsplash.com/photo-1616394584738-fc6e612e71b9?auto=format&fit=crop&w=600&q=80" alt="Skin glow herbal care" class="h-24 w-full rounded-lg object-cover">
                    <span>Skin Glow</span>
                </a>
                <a href="pages/products.php?q=hair" class="concern-card reveal-up rounded-xl border border-gray-200 bg-white p-2 text-center font-medium hover:border-green-300" style="transition-delay:.12s;">
                    <img src="https://images.unsplash.com/photo-1522337660859-02fbefca4702?auto=format&fit=crop&w=600&q=80" alt="Hair care herbs" class="h-24 w-full rounded-lg object-cover">
                    <span>Hair Care</span>
                </a>
                <a href="pages/products.php?q=stress" class="concern-card reveal-up rounded-xl border border-gray-200 bg-white p-2 text-center font-medium hover:border-green-300" style="transition-delay:.16s;">
                    <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=600&q=80" alt="Stress relief tea" class="h-24 w-full rounded-lg object-cover">
                    <span>Stress Relief</span>
                </a>
                <a href="pages/products.php?q=immunity" class="concern-card reveal-up rounded-xl border border-gray-200 bg-white p-2 text-center font-medium hover:border-green-300" style="transition-delay:.2s;">
                    <img src="https://images.unsplash.com/photo-1471193945509-9ad0617afabf?auto=format&fit=crop&w=600&q=80" alt="Immunity boosting herbs" class="h-24 w-full rounded-lg object-cover">
                    <span>Immunity Boost</span>
                </a>
                <a href="pages/products.php?q=digestion" class="concern-card reveal-up rounded-xl border border-gray-200 bg-white p-2 text-center font-medium hover:border-green-300" style="transition-delay:.24s;">
                    <img src="https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=600&q=80" alt="Digestion support herbs" class="h-24 w-full rounded-lg object-cover">
                    <span>Digestion</span>
                </a>
            </div>
        </section>

        <section class="mt-10 p-5 reveal-up trust-strip">
            <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
                <div class="reveal-up trust-pill" style="transition-delay:.06s;">
                    <span class="trust-icon">✓</span>
                    <span>Clinically inspired herbal blends</span>
                </div>
                <div class="reveal-up trust-pill" style="transition-delay:.12s;">
                    <span class="trust-icon">🌿</span>
                    <span>Ethically sourced ingredients</span>
                </div>
                <div class="reveal-up trust-pill" style="transition-delay:.18s;">
                    <span class="trust-icon">★</span>
                    <span>Trusted by wellness-focused customers</span>
                </div>
            </div>
        </section>
    </main>
    <?php require_once __DIR__ . '/../shared-core/includes/layout/footer.php'; ?>

    <?php if (!$isLoggedIn): ?>
        <div id="login-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">Login Required</h3>
                    <button type="button" data-login-close class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-100 hover:bg-gray-100 hover:text-gray-600">✕</button>
                </div>
                <p class="text-gray-600 mb-4">Please sign in to add items to your cart.</p>
                <form method="POST" action="pages/login.php" class="space-y-4">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                    <div>
                        <label class="block mb-1 font-semibold" for="login-identifier">Email or Username</label>
                        <input class="w-full border border-gray-300 rounded-lg p-2" type="text" name="identifier" id="login-identifier" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold" for="login-password">Password</label>
                        <input class="w-full border border-gray-300 rounded-lg p-2" type="password" name="password" id="login-password" required>
                    </div>
                    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">Sign In</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const revealElements = document.querySelectorAll('.reveal-up');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.16
        });

        revealElements.forEach((element) => revealObserver.observe(element));

        const loginModal = document.getElementById('login-modal');
        if (loginModal) {
            const loginButtons = document.querySelectorAll('[data-login-required]');
            loginButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    loginModal.classList.remove('hidden');
                    loginModal.classList.add('flex');
                });
            });
            const closeButtons = loginModal.querySelectorAll('[data-login-close]');
            closeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    loginModal.classList.add('hidden');
                    loginModal.classList.remove('flex');
                });
            });
            loginModal.addEventListener('click', (event) => {
                if (event.target === loginModal) {
                    loginModal.classList.add('hidden');
                    loginModal.classList.remove('flex');
                }
            });
        }
    </script>
</body>

</html>