<?php

namespace Arall\CMSDiff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Arall\CMSDiff\GitHub\Repository;

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

        try {
            $repository = new Repository($repo);
        } catch (\Exception $e) {
            return $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        // Get tags
        $output->writeln('<info>Loading repository tags</info>');
        $tags = $repository->getTags();

        // Has tags?
        if (!is_array($tags) || empty($tags)) {
            return $output->writeln('<error>No tags found</error>');
        }
        $output->writeln(count($tags). ' tags found (from <comment>'.current($tags)->name.'</comment> to <comment>'.end($tags)->name.'</comment>)');
        $output->writeln('');

        // Check download path
        if (!is_dir($path) || !is_writable($path)) {
            return $output->writeln('<error>Destination path '.$path.' is not writable</error>');
        }

        // Product folder
        $productPath = $path . DIRECTORY_SEPARATOR . $repository->repo;

        // Download all relases
        foreach ($tags as $tag) {
            $output->writeln('  - Downloading release <info>'.$repo.'</info> (<comment>'.$tag->name.'</comment>)');

            if ($repository->downloadRelease($tag->name, $productPath)) {
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
