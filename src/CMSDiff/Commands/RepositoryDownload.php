<?php

namespace Arall\CMSDiff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Arall\CMSDiff\Downloader\Providers\GitHub;
use Arall\CMSDiff\Downloader\Downloader;
use InvalidArgumentException;

class RepositoryDownload extends Command
{
    public function configure()
    {
        $this
            ->setName('repository:download')
            ->setDescription('Download all releases of a given GitHub repository')
            ->addArgument(
                'repo',
                InputArgument::REQUIRED,
                'GitHub repository name (joomla/joomla-cms)'
            )
            ->addOption(
               'path',
               'p',
               InputOption::VALUE_OPTIONAL,
               'Download path'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $input->getArgument('repo');
        $path = $input->getOption('path') ?: 'data';

        // Repository
        $tmp = explode('/', $repo);
        if (count($tmp) != 2) {
            throw new InvalidArgumentException('Invalid repository name: '.$repo);
        }
        $owner = $tmp[0];
        $repo = $tmp[1];

        // Product folder
        $productPath = $path.DIRECTORY_SEPARATOR.$owner.DIRECTORY_SEPARATOR.$repo;

        // Classes
        $provider = new GitHub($owner, $repo);
        $downloader = new Downloader($provider, $productPath);

        // Get releases
        $output->writeln('<info>Loading repository releases</info>');
        $releases = $provider->getReleases();

        // Has releases?
        if (!is_array($releases) || empty($releases)) {
            return $output->writeln('<error>No releases found</error>');
        }
        $output->writeln(count($releases).' releases found (from <comment>'.current($releases)->name.'</comment> to <comment>'.end($releases)->name.'</comment>)');
        $output->writeln('');

        // Check download path
        if (!is_dir($path) || !is_writable($path)) {
            return $output->writeln('<error>Destination path '.$path.' is not writable</error>');
        }

        // Download all relases
        foreach ($releases as $release) {
            $output->writeln('  - Downloading release <info>'.$repo.'</info> (<comment>'.$release->name.'</comment>)');

            // Download release
            if ($downloader->download($release->name)) {
                $output->writeln('  - Downloaded!');
                $output->writeln('');
                continue;
            }

            // Download error
            $output->writeln('  - <error>Download error</error>');
            $output->writeln('');
        }

        $output->writeln('All releases have been downloaded');
        $output->writeln('');
    }
}
