<?php
 namespace Davidybertha\Github\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

 class GithubActivityCommand extends Command
 {
    protected static $defaultname = "github:activity";
    
    protected function configure()
    {
        $this
        ->setName('github:activity')
        ->setDescription('Verificar la actividad del usuario en Github.')
        ->addArgument('username', InputArgument::REQUIRED, 'Usuario de github');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        if (!$username) {
            $output->writeln('<error>No username provided. Please provide a GitHub username.</error>');
            return Command::FAILURE;
        }

        try {
            $data = $this->fetchUserEvents($username);
            if (empty($data)) {
                $output->writeln("<info>No activity found for the user: {$username}</info>");
            } else {
                $output->writeln("<info>Activity for user: {$username}</info>");
                $this->displayUserEvents($output, $data);
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error fetching data: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Fetches user events from the GitHub API.
     *
     * @param string $username
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function fetchUserEvents(string $username): array
    {
        $client = new Client();
        $response = $client->request('GET', "https://api.github.com/users/{$username}/events");
        return json_decode($response->getBody(), true) ?? [];
    }

    /**
     * Displays user events in the console.
     *
     * @param OutputInterface $output
     * @param array $data
     * @return void
     */
    private function displayUserEvents(OutputInterface $output, array $data): void
    {
        foreach ($data as $event) {
            $output->writeln(sprintf(
                "- Event: %s | Repo: %s",
                $event['type'] ?? 'Unknown',
                $event['repo']['name'] ?? 'Unknown'
            ));
        }
    }
}