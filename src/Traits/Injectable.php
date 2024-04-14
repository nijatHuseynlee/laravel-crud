<?php

namespace Nijat\LaravelCrud\Traits;

use Illuminate\Support\Facades\File;

trait Injectable
{
    /**
     * @param string $subject
     * @return array
     */
    public function inject(string $subject): array
    {
        $providers = [];

        $subjectFolders = config('crud.' . $subject, []);
        $contractForm = config('crud.contract_form', '{class}Interface');

        foreach ($subjectFolders as $contractNamespace => $subjectNamespace) {
            $subjectFolder = lcfirst(str_replace("\\", "/", $subjectNamespace));

            $subjects = File::files(base_path($subjectFolder));

            foreach($subjects as $subject) {
                $subjectName = pathinfo($subject)['filename'];
                $contractName = str_replace('{class}', $subjectName, $contractForm);
                $providers[$contractNamespace . "\\" . $contractName] = $subjectNamespace . "\\" . $subjectName;
            }
        }

        return $providers;
    }
}
