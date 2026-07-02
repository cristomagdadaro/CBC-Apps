<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">Local Setup and Docker Deployment</h3>
            <p v-if="showDeveloperSections" class="mb-3">This guide covers how to set up the {{ $appName }} project locally using Docker, as well as how to deploy it on a Linux server using the Docker CLI.</p>
            <p v-else class="mb-3">This guide provides technical instructions for developers and administrators on setting up and deploying the system.</p>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">Prerequisites</h3>
            <ul class="list-disc list-inside space-y-2 mb-4">
                <li><strong>Docker:</strong> Install Docker Desktop (for Windows/Mac) or Docker Engine (for Linux).</li>
                <li><strong>Git:</strong> To clone the repository.</li>
            </ul>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">1. Local Setup via Docker Compose</h3>
            <p class="mb-3">Our setup uses a multi-container Docker architecture (App, Web, Database, and Worker). Frontend assets and PHP dependencies are built automatically within the Dockerfile.</p>
            <ol class="list-decimal list-inside space-y-4">
                <li>
                    <strong>Clone the repository:</strong>
                    <pre class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>git clone &lt;repository-url&gt;
cd CBC-Apps</code></pre>
                </li>
                <li>
                    <strong>Set up your environment file:</strong>
                    <pre class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>cp .env.example .env</code></pre>
                    <p class="text-sm mt-1">Configure your <code>.env</code> file. For local testing without a mail server, set <code>MAIL_MAILER=log</code> to avoid seeder errors.</p>
                </li>
                <li>
                    <strong>Build and Start Docker Services:</strong>
                    <pre class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>docker compose up -d --build</code></pre>
                </li>
                <li>
                    <strong>Fix local directory permissions:</strong>
                    <p class="text-sm mt-1 mb-2">Since we mount the local directory to the container, we need to ensure the container's web server can write to the storage folders.</p>
                    <pre class="text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>docker compose exec app chmod -R 777 storage bootstrap/cache</code></pre>
                </li>
                <li>
                    <strong>Generate application key and run migrations:</strong>
                    <pre class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed</code></pre>
                </li>
                <li>
                    <strong>Access the application:</strong>
                    <p class="mt-1">The application should now be accessible locally at <a href="http://localhost" class="text-blue-500 underline" target="_blank">http://localhost</a>.</p>
                </li>
            </ol>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">2. Server-Side Deployment via Linux Docker CLI</h3>
            <p class="mb-3">For a production or staging Linux server, the process is very similar but requires your SSL certificates and production environment variables.</p>
            <ol class="list-decimal list-inside space-y-4">
                <li>
                    <strong>Server Requirements:</strong>
                    <p class="text-sm mt-1">Ensure Docker Engine and Docker Compose plugin are installed on your Linux server.</p>
                </li>
                <li>
                    <strong>Clone and Configure:</strong>
                    <pre class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>git clone &lt;repository-url&gt;
cd CBC-Apps
cp .env.example .env</code></pre>
                    <p class="text-sm mt-1">Update the <code>.env</code> file with production settings (e.g., <code>APP_ENV=production</code>, <code>APP_DEBUG=false</code>, DB credentials, Mail settings).</p>
                </li>
                <li>
                    <strong>Configure SSL Certificates:</strong>
                    <p class="text-sm mt-1 mb-2">Place your SSL certificates in the <code>/etc/nginx/certs</code> directory on your host server.</p>
                    <p class="text-sm mb-2">Then, edit <code>docker/nginx/default.conf</code> and uncomment the SSL configuration block (lines 4-10).</p>
                </li>
                <li>
                    <strong>Build and Run Containers:</strong>
                    <pre class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>docker compose up -d --build</code></pre>
                </li>
                <li>
                    <strong>Execute Final Setup Commands:</strong>
                    <pre class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded"><code>docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear</code></pre>
                </li>
            </ol>
        </section>
    </div>
</template>

<script>
export default {
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
    name: 'LocalDeploymentTopic',
}
</script>
