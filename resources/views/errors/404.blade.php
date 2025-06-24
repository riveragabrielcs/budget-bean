<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found | BudgetBean</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom BudgetBean Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'budgetbean': {
                            'green': '#10B981',
                            'peach': '#FFEDD5',
                            'gold': '#FBBF24'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }



        .fade-in {
            animation: fade-in 0.8s ease-out;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-green-50 min-h-screen flex items-center justify-center p-4">
<div class="max-w-2xl mx-auto text-center fade-in">
    <!-- Error Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-emerald-100 p-8 md:p-12">
        <!-- BudgetBean Image -->
        <div class="mb-8 flex justify-center">
            <div class="bean-bounce">
                <img src="{{ asset('images/BudgetBean404.png') }}"
                     alt="BudgetBean looking for lost page"
                     class="w-64 h-auto max-w-full">
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-amber-100 text-amber-800 border border-amber-200">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Error 404
                </span>
        </div>

        <!-- Main Heading -->
        <h1 class="text-3xl md:text-4xl font-bold text-emerald-800 mb-4">
            Page Not Found
        </h1>

        <!-- Custom Message -->
        <p class="text-lg text-stone-600 mb-8 leading-relaxed">
            Oops… we budgeted wrong and now the page is lost like a penny in the couch cushions.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}"
               class="inline-flex items-center px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-xl shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                Plant Me Back Home
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center">
        <p class="text-stone-500 text-sm">
            © {{ date('Y') }} BudgetBean. Big change starts with a little bean. 🌱
        </p>
    </div>
</div>
</body>
</html>
