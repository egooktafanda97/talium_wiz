<?php

namespace TaliumAbstract\Commads;

trait RepositoryFileGenerator
{

    public function handle()
    {
        $modelName = $this->argument('modelName');
        // $makeModel = $this->option('m');
        // $makeController = $this->option('c');

        // Parsing Namespace dan Nama Class
        $segments = explode('\\', $modelName);
        $className = array_pop($segments);
        $namespace = implode('\\', $segments);

        // Membuat Direktori
        $directory = app_path('Services/' . str_replace('\\', '/', $namespace));
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Membuat File Service
        $serviceStub = \File::get(__DIR__ . '/../stub/ServiceStub.stub');
        $serviceContent = str_replace('{{ ModelName }}', $className, $serviceStub);
        $serviceContent = str_replace('{{ namespace }}',  "\\" . $namespace, $serviceContent);
        $serviceFileName = $directory . '/' . $className . 'Service.php';
        \File::put($serviceFileName, $serviceContent);


        // Membuat Direktori Repository
        $repositoryDirectory = app_path('Repositories/' . str_replace('\\', '/', $namespace));

        if (!file_exists($repositoryDirectory)) {
            mkdir($repositoryDirectory, 0755, true);
        }

        // Membuat File Repository
        $repositoryStub = \File::get(__DIR__ . '/../stub/RepositoryStub.stub');
        $repositoryContent = str_replace('{{ ModelName }}', $className, $repositoryStub);
        $repositoryContent = str_replace('{{ namespace }}', "\\" . $namespace, $repositoryContent);
        $repositoryFileName = $repositoryDirectory . '/' . $className . 'Repository.php';
        \File::put($repositoryFileName, $repositoryContent);

        $this->info('Files generated successfully!');
    }
}
