<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="POST" action="log_action.php" class="space-y-4">
        <input type="hidden" name="document_id" value="123">
        <input type="hidden" name="user_id" value="5">

        <label>
            Select Action:
            <select name="action" class="border p-2 rounded">
                <option value="view">View</option>
                <option value="download">Download</option>
                <option value="edit">Edit</option>
            </select>
        </label>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Log Action</button>
    </form>

</body>

</html>