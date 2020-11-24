# Meta data file

The MetaDataFile Class is a simple data storage which writes data in a json format to a file.
It is useful if you for example have to query APIs in a cronjob and you need to store the last successfully execution time...

The [ProcessManagerSampleCommandSimple](sample/src/AppBundle/Command/ProcessManagerSampleCommandSimple.php) shows a basic usage.

Getting the file and writing data:
```php
use Elements\Bundle\ProcessManagerBundle\MetaDataFile;

$metDataFileObject = MetaDataFile::getById('spample-id');
$data = $metDataFileObject->getData();

$metDataFileObject->setData(['lastRun' => $start->getTimestamp()])->save();
```