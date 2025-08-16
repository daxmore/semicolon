<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Semicolon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Hero Section for About Us -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">About Semicolon</h1>
            <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
                At Semicolon, we are passionate about empowering learners and researchers with accessible, high-quality educational resources. Our platform is designed to be a comprehensive hub for academic papers, digital books, and insightful video content.
            </p>
        </div>

        <!-- Mission Section -->
        <div class="bg-white rounded-lg shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Our Mission</h2>
            <div class="flex flex-col md:flex-row items-center md:space-x-8">
                <div class="md:w-1/3 mb-6 md:mb-0">
                    <img src="https://images.unsplash.com/photo-1706062070584-25a61fc4bc46?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Our Mission" class="rounded-lg shadow-md w-full h-96 object-cover">
                </div>
                <div class="md:w-2/3">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Our mission is to democratize knowledge by providing a seamless and intuitive platform where students, educators, and lifelong learners can discover, access, and engage with a diverse range of academic materials. We strive to foster a community of continuous learning and intellectual growth.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        We believe that education should be a right, not a privilege, and by making resources readily available, we contribute to a more informed and educated global society.
                    </p>
                </div>
            </div>
        </div>

        <!-- Vision Section -->
        <div class="bg-white rounded-lg shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Our Vision</h2>
            <div class="flex flex-col md:flex-row-reverse items-center md:space-x-reverse md:space-x-8">
                <div class="md:w-1/3 mb-6 md:mb-0">
                    <img src="https://plus.unsplash.com/premium_photo-1753426003820-74e3b9d515d7?q=80&w=1074&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Our Vision" class="rounded-lg shadow-md w-full h-96 object-cover">
                </div>
                <div class="md:w-2/3">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We envision a future where every individual has the tools and resources to pursue their academic and personal development goals without barriers. Semicolon aims to be the leading online repository for educational content, recognized for its quality, accessibility, and user-centric design.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        Through innovation and collaboration, we aspire to continuously expand our offerings and integrate cutting-edge technologies to enhance the learning experience for our users worldwide.
                    </p>
                </div>
            </div>
        </div>

        <!-- Values Section (Optional, can be added if needed) -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Our Core Values</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-blue-50 p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-blue-800 mb-3">Accessibility</h3>
                    <p class="text-gray-700">Ensuring knowledge is available to everyone, everywhere.</p>
                </div>
                <div class="bg-green-50 p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-green-800 mb-3">Quality</h3>
                    <p class="text-gray-700">Providing accurate, reliable, and valuable resources.</p>
                </div>
                <div class="bg-purple-50 p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-purple-800 mb-3">Innovation</h3>
                    <p class="text-gray-700">Continuously improving and adapting to user needs.</p>
                </div>
            </div>
        </div>

        <!-- Call to Action / Contact Section -->
        <div class="text-center bg-gray-100 rounded-lg p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Join Our Journey</h2>
            <p class="text-lg text-gray-600 mb-6">Have questions or want to collaborate? We'd love to hear from you!</p>
            <a href="contact.php" class="inline-block bg-teal-600 text-white hover:bg-teal-700 px-8 py-3 rounded-full text-lg font-semibold transition duration-300 ease-in-out shadow-md">
                Contact Us
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>