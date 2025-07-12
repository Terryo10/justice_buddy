<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Justice Buddy API - Welcome</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles -->
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                color: #333;
                line-height: 1.6;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }

            .header {
                text-align: center;
                margin-bottom: 3rem;
            }

            .logo {
                font-size: 3rem;
                font-weight: 700;
                color: white;
                margin-bottom: 1rem;
                text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }

            .subtitle {
                font-size: 1.2rem;
                color: rgba(255,255,255,0.9);
                margin-bottom: 2rem;
            }

            .main-content {
                background: white;
                border-radius: 20px;
                padding: 3rem;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                margin-bottom: 2rem;
            }

            .welcome-section {
                text-align: center;
                margin-bottom: 3rem;
            }

            .welcome-title {
                font-size: 2.5rem;
                font-weight: 600;
                color: #333;
                margin-bottom: 1rem;
            }

            .welcome-text {
                font-size: 1.1rem;
                color: #666;
                max-width: 600px;
                margin: 0 auto;
            }

            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
                margin-bottom: 3rem;
            }

            .feature-card {
                background: #f8f9fa;
                padding: 2rem;
                border-radius: 15px;
                border-left: 4px solid #667eea;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }

            .feature-icon {
                font-size: 2rem;
                margin-bottom: 1rem;
                color: #667eea;
            }

            .feature-title {
                font-size: 1.3rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: #333;
            }

            .feature-description {
                color: #666;
                line-height: 1.6;
            }

            .api-endpoints {
                background: #f8f9fa;
                padding: 2rem;
                border-radius: 15px;
                margin-bottom: 2rem;
            }

            .endpoints-title {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 1.5rem;
                color: #333;
            }

            .endpoint-list {
                list-style: none;
            }

            .endpoint-item {
                display: flex;
                align-items: center;
                padding: 0.75rem 0;
                border-bottom: 1px solid #e9ecef;
            }

            .endpoint-item:last-child {
                border-bottom: none;
            }

            .method {
                padding: 0.25rem 0.75rem;
                border-radius: 6px;
                font-size: 0.8rem;
                font-weight: 600;
                margin-right: 1rem;
                min-width: 60px;
                text-align: center;
            }

            .method.get { background: #d4edda; color: #155724; }
            .method.post { background: #d1ecf1; color: #0c5460; }
            .method.put { background: #fff3cd; color: #856404; }
            .method.delete { background: #f8d7da; color: #721c24; }

            .endpoint-path {
                font-family: 'Monaco', 'Menlo', monospace;
                color: #495057;
                flex: 1;
            }

            .actions {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
                font-size: 1rem;
            }

            .btn-primary {
                background: #667eea;
                color: white;
            }

            .btn-primary:hover {
                background: #5a6fd8;
                transform: translateY(-2px);
            }

            .btn-secondary {
                background: transparent;
                color: #667eea;
                border: 2px solid #667eea;
            }

            .btn-secondary:hover {
                background: #667eea;
                color: white;
            }

            .footer {
                text-align: center;
                color: rgba(255,255,255,0.8);
                margin-top: 2rem;
            }

            @media (max-width: 768px) {
                .container {
                    padding: 1rem;
                }
                
                .main-content {
                    padding: 2rem 1.5rem;
                }
                
                .logo {
                    font-size: 2rem;
                }
                
                .welcome-title {
                    font-size: 2rem;
                }
                
                .features-grid {
                    grid-template-columns: 1fr;
                }
                
                .actions {
                    flex-direction: column;
                }
                
                .btn {
                    width: 100%;
                    text-align: center;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header class="header">
                <div class="logo">⚖️ Justice Buddy API</div>
                <div class="subtitle">Empowering legal assistance through intelligent technology</div>
            </header>

            <main class="main-content">
                <section class="welcome-section">
                    <h1 class="welcome-title">Welcome to Justice Buddy API</h1>
                    <p class="welcome-text">
                        Your comprehensive legal assistance platform API. Access legal information, 
                        connect with lawyers, generate documents, and get AI-powered legal guidance 
                        through our robust REST API.
                    </p>
                </section>

                <section class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🤖</div>
                        <h3 class="feature-title">AI Legal Assistant</h3>
                        <p class="feature-description">
                            Get intelligent legal advice and guidance through our advanced AI-powered 
                            chat system that understands legal contexts and provides relevant information.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">👨‍💼</div>
                        <h3 class="feature-title">Lawyer Directory</h3>
                        <p class="feature-description">
                            Connect with qualified legal professionals in your area. Browse profiles, 
                            specialties, and contact information for lawyers across various practice areas.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📄</div>
                        <h3 class="feature-title">Document Generation</h3>
                        <p class="feature-description">
                            Generate legal documents, letters, and forms automatically. Our system 
                            creates customized legal documents based on your specific requirements.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📚</div>
                        <h3 class="feature-title">Legal Information</h3>
                        <p class="feature-description">
                            Access comprehensive legal information, case studies, and educational 
                            resources to better understand your legal rights and options.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📋</div>
                        <h3 class="feature-title">Letter Templates</h3>
                        <p class="feature-description">
                            Use our collection of legal letter templates for various purposes. 
                            Customize and generate professional legal correspondence quickly.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3 class="feature-title">Secure & Reliable</h3>
                        <p class="feature-description">
                            Built with security in mind. All data is encrypted and protected. 
                            Our API ensures your legal information remains confidential and secure.
                        </p>
                    </div>
                </section>

                <section class="api-endpoints">
                    <h2 class="endpoints-title">Available API Endpoints</h2>
                    <ul class="endpoint-list">
                        <li class="endpoint-item">
                            <span class="method get">GET</span>
                            <span class="endpoint-path">/api/categories</span>
                        </li>
                        <li class="endpoint-item">
                            <span class="method get">GET</span>
                            <span class="endpoint-path">/api/lawyers</span>
                        </li>
                        <li class="endpoint-item">
                            <span class="method get">GET</span>
                            <span class="endpoint-path">/api/law-info</span>
                        </li>
                        <li class="endpoint-item">
                            <span class="method post">POST</span>
                            <span class="endpoint-path">/api/chat</span>
                        </li>
                        <li class="endpoint-item">
                            <span class="method post">POST</span>
                            <span class="endpoint-path">/api/letters/generate</span>
                        </li>
                        <li class="endpoint-item">
                            <span class="method get">GET</span>
                            <span class="endpoint-path">/api/documents</span>
                        </li>
                    </ul>
                </section>

                <section class="actions">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/admin') }}" class="btn btn-primary">Admin Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">Admin Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
                            @endif
                        @endauth
                    @endif
                    
                    <a href="https://www.linkedin.com/in/tererai/" class="btn btn-secondary" target="_blank">Connect on LinkedIn</a>
                    <a href="https://github.com/Terryo10/justice_buddy" class="btn btn-secondary" target="_blank">GitHub Repository</a>
                </section>
            </main>

            <footer class="footer">
                <p>&copy; {{ date('Y') }} Justice Buddy API. Built with Laravel and ❤️ for the legal community.</p>
                <p>Developed by <strong>Tapiwa Tererai</strong></p>
            </footer>
        </div>
    </body>
</html>
