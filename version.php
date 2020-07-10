<?php
$composer          = json_decode(file_get_contents('composer.json'));
$originalVersion   = $composer->version;
//To explode the parts properly we add a . to the -beta part of the string
$composer->version = str_replace('-beta', '.-beta', $originalVersion);
$versionParts      = explode('.', $composer->version);

if ($_SERVER['argv'][1] ?? false) {
    $reason = $_SERVER['argv'][1] . ': Version update';
    switch ($_SERVER['argv'][1]) {
        case 'fix':
            $versionParts[2] = $versionParts[2] + 1;
            break;
        case 'minor':
            $versionParts[1] = $versionParts[1] + 1;
            $versionParts[2] = 0;
            break;
        case 'major':
            $versionParts[0] = $versionParts[0] + 1;
            $versionParts[2] = 0;
            $versionParts[1] = 0;
            break;
        case 'beta':
            $isFix           = ($_SERVER['argv'][2] ?? false) === 'fix';
            $versionParts[1] = $isFix ? $versionParts[1] : $versionParts[1] + 1;
            $versionParts[2] = $isFix ? $versionParts[2] + 1 : 0;
            $versionParts[3] = '-beta';
            break;
    }
}

//compose does not accept .- combination so we remove it here
$composer->version = str_replace('.-beta', '-beta', join('.', $versionParts));

if ($originalVersion !== $composer->version) {

    exec('git status', $out);
    $output = join(PHP_EOL, $out);
    if (strpos($output, 'nothing ') !== false || array_search('--force', $_SERVER['argv'])) {
        file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        exec('git add .');
        exec(sprintf('git commit -m "%s"', $reason));
        exec(sprintf('git tag -a %s -m "%s"', $composer->version, $reason));
        echo '=== Version update done / new version ' . $composer->version . ' ===' . PHP_EOL;
        echo '++ Run "git push && git push origin --tags" ++' . PHP_EOL;
        echo 'git push && git push origin --tags' . PHP_EOL;
    } else {
        echo '=============== Git status =====================' . PHP_EOL;
        echo $output;
        echo PHP_EOL . PHP_EOL;
        echo '!! Clean or commit workspace changes before update the package version !!' . PHP_EOL . PHP_EOL;
    }
} else {
    print ' Opties: 
    - fix
    - minor
    - major
    - beta [fix]  -> beta creates new minor beta tag, -> beta tag creates beta fix tag on existing beta minor
';

}
