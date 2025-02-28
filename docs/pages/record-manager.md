# Record Manager

Record manager is a powerul utility to perform CRUD-like operations (create, read, update & delete) over complex data sets. By default, record manager uses `Eloquent` as a data-access layer, but this can be easily modified to other ORM libraries or even RESTful API access.

For example, following very simple code:

```php
$mContact = new \MyApp\Models\Contact($this->app);
$contact = $mContact->record->read($mContact->record->prepareReadQuery()->where('id', 1));
```

will produce an array (`$contact`) containing the *record* with contact ID = 1. It will have the following structure (example):

```
[
  "CATEGORY" => [
    "name" => "Gold partner",
    "color" => "#FFFF00",
  ],
  "TAGS" => [
    [
      "tag" => "Important"
    ],
    [
      "tag" => "Follow-up required"
    ]
  ],
  "first_name" => "John",
  "last_name" => "Smith",
  "id_category" => 1,
  "_LOOKUP[id_category]" => "Gold partner",
]
```

The structure above is a `record`. These records are extremly useful when using an API to retrieve the data or to create/update complex data at once. For example, *tables* are using these structures to render rows and columns and *forms* are using these structures to render inputs.

## Understanding `records`

Records are data items containing not only raw data from the SQL table, but also some other information:

  * raw data from relations
  * lookup values
  * enum values

### Raw data

First, records contain raw data from the SQL table. These are the data for columns specified in model's `describeColumns()` method.

For example, the above record would be a result of the following setup:

```php
public function describeColumns(): array
{
  return array_merge(parent::describeColumns(), [
    'first_name' => new \ADIOS\Core\Db\Column\Varchar($this, 'First name'),
    'last_name' => new \ADIOS\Core\Db\Column\Varchar($this, 'Last name'),
    'last_name' => new \ADIOS\Core\Db\Column\Varchar($this, 'Last name'),
    'id_category' => new \ADIOS\Core\Db\Column\Lookup($this, 'Category', Category::class),
  ]);
}
```

### Raw data from relations

In the example below, we have a *lookup* column that is representing a `1:N` relation. In other words, the contact has a category specified by ID of the category. Then, using a relation, we can get other data for that category. To describe such relation, following code must be included in the definition of the `Contact` model:

```php
public array $relations = [
  'CATEGORY' => [
    self::BELONGS_TO, // type of relation
    Category::class, // class of the related model
    'id_category', // column of the master model storing the related ID
    'id' // column of the related model storing the related ID
    ],
];
```

With this setup, the `read()` method of the `Record` will include the `CATEGORY` relation in the resulting record.

There are, basically, two types of relations:

  * `self::BELONGS_TO` - a `1:N` relation
  * `self::HAS_MANY` - a `M:N` relation

### Lookup values

In the example record, there is a `_LOOKUP[id_category]` key. This key holds a textual representation of the lookup-type column (in our case, the *id_category*). This value is used, e.g. in tables, forms or inputs.

Lookup values are included in the records automatically - this is provided by the `Lookup` column type.

### Enum values

*Integer* and *Varchar* column types can have enum values specified. These are very similar to lookups, with one significant difference - the list of enum values is hardcoded in your code, contrary to lookups for which the values are loaded from a related SQL table.

So, when a column is described followingly:

```php
public function describeColumns(): array
{
  return array_merge(parent::describeColumns(), [
    'contact_type' => (new \ADIOS\Core\Db\Column\Int($this, 'Contact type')),
      ->setEnumValues([
        1 => 'Phone number',
        2 => 'Email',
        3 => 'Other'
      ])
  ]);
}
```

Then the values in the SQL would be either `1` or `2` or `3` and will be visually represented (in *tables*, *forms* or *inputs*) as `Phone number` or `Email` or `Other`, respectively. And, the record returned by `read()` method of the Record would contain:

```
[
  "_ENUM[contact_type]" => "Phone number",
]
```

## CRUD-like operations

### Creating records

To create a record, simply run:

```php
$mContact->record->create([
  "first_name" => "John",
  "last_name" => "Smith",
  "id_category" => 1,
])
```

### Reading records

To read a record, simply run:

```php
$readQuery = $mContact->record->prepareReadQuery(); // prepare the query for reading
$readQuery->where('id', 1); // use Eloquent to modify the query
$record = $mContact->record->read($readQuery); // read record
```

### Updating records

To update a record, simply run:

```php
$mContact->record->update([
  "id" => 1,
  "first_name" => "John",
  "last_name" => "Smith",
  "id_category" => 1,
])
```
