<?php

namespace niolab\sentry\helpers;

use Yii;

class GitInfo
{
    public static function branch(): ?string
    {
        return self::info()['branch'];
    }

    public static function commit(): ?string
    {
        return self::info()['commit'];
    }

    public static function info(): ?array
    {
        $gitBasePath = Yii::getAlias('@root') . '/.git';

        $path =  $gitBasePath . '/HEAD';

        if (file_exists($path)) {
            $gitBranchName = rtrim(preg_replace("/(.*?\/){2}/", '', file_get_contents($path)));

            $gitPathBranch = $gitBasePath . '/refs/heads/' . $gitBranchName;

            if (file_exists($gitPathBranch)) {
                $gitHash = file_get_contents($gitPathBranch);
                $gitDate = date(DATE_ATOM, filemtime($gitPathBranch));
            }
        }

        return [
            'date'   => $gitDate ?? null,
            'branch' => $gitBranchName ?? null,
            'commit' => $gitHash ?? null,
        ];
    }
}