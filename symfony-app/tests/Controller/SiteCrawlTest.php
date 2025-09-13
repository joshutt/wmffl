<?php

namespace Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

// Assuming you have an Article entity

/**
 * This functional test crawls the entire site to ensure no pages return an error.
 * It discovers URLs by inspecting the application's routes.
 */
class SiteCrawlTest extends WebTestCase
{
    /**
     * This is the main test method. It receives a URL from the urlProvider
     * and asserts that the page loads successfully.
     *
     * @dataProvider urlProvider
     */
    public function testAllPagesLoadSuccessfully(string $url, string $routeName): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        // Assert that the response is successful (status code 2xx)
        $this->assertTrue(
            $client->getResponse()->isSuccessful(),
            sprintf('The page "%s" (route: %s) failed to load successfully.', $url, $routeName)
        );
    }

    /**
     * This method acts as a data provider for the test.
     * It inspects all routes and generates a list of URLs to test.
     */
    public function urlProvider(): Generator
    {
        // Boot the kernel to access the service container
        self::bootKernel();
        $container = static::getContainer();

        /** @var RouterInterface $router */
        $router = $container->get('router');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        $routes = $router->getRouteCollection()->all();

        /** @var Route $route */
        foreach ($routes as $routeName => $route) {
            // --- Skip routes that are not suitable for this test ---

            // Skip non-GET routes
            if (!in_array('GET', $route->getMethods()) && !empty($route->getMethods())) {
                continue;
            }

            // Skip internal Symfony routes (like profiler, error pages, etc.)
            if (str_starts_with($routeName, '_')) {
                continue;
            }

            // --- Generate URLs ---

            $path = $route->getPath();
            $params = [];

            // Check if the route has required parameters (e.g., {slug}, {id})
            if (preg_match_all('/\{(\w+)\}/', $path, $matches)) {
                $requiredParams = $matches[1];
                $paramsFound = true;

                foreach ($requiredParams as $paramName) {
                    // Add logic here to find valid values for your parameters
                    switch ($paramName) {
                        case 'slug':
                            // Find a real article from the database to get a valid slug
                            $article = $entityManager->getRepository(Article::class)->findOneBy([]);
                            if ($article) {
                                $params['slug'] = $article->getSlug();
                            } else {
                                $paramsFound = false; // Can't find an article, so can't test this route
                            }
                            break;

                        case 'year':
                        case 'season':
                            // Use a sensible default for year/season
                            $params[$paramName] = date('Y');
                            break;

                        // Add more cases for other parameters your site might have
                        // case 'id':
                        //     $someEntity = $entityManager->getRepository(SomeEntity::class)->findOneBy([]);
                        //     if ($someEntity) { $params['id'] = $someEntity->getId(); } else { $paramsFound = false; }
                        //     break;

                        default:
                            // If we don't know how to handle a parameter, we must skip this route
                            $paramsFound = false;
                            break;
                    }
                }

                if (!$paramsFound) {
                    continue; // Skip routes where we couldn't find required parameters
                }
            }

            // Generate the final URL and yield it for the test
            $url = $router->generate($routeName, $params);
            yield $url => [$url, $routeName];
        }
    }
}