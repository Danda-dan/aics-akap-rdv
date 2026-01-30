import dedupe
from dedupe import variables

# Sample data with duplicates and non-duplicates
data = {
    '1': {'name': 'John Smith', 'email': 'john@example.com'},
    '2': {'name': 'Jon Smith', 'email': 'jon@example.com'},
    '3': {'name': 'John Smyth', 'email': 'johnny@example.com'},
    '4': {'name': 'Mary Jones', 'email': 'mary@example.com'},
    '5': {'name': 'Marie Jones', 'email': 'marie@example.com'},
    '6': {'name': 'M. Jones', 'email': 'mjones@example.com'},
    '7': {'name': 'Bob Lee', 'email': 'bob@example.com'},
    '8': {'name': 'Robert Lee', 'email': 'robert@example.com'},
    '9': {'name': 'Alice Kim', 'email': 'alice@example.com'},
    '10': {'name': 'Alicia Kim', 'email': 'alicia@example.com'},
    '11': {'name': 'J. Smith', 'email': 'john.smith@example.com'},
    '12': {'name': 'Mary A. Jones', 'email': 'maryj@example.com'},
    '13': {'name': 'Bobby Lee', 'email': 'bob@example.com'},  # dup with 7
    '14': {'name': 'Mary Jones', 'email': 'maryjones@example.com'},
    '15': {'name': 'Alice K.', 'email': 'alice.kim@example.com'}  # dup with 9
}

# Define field types
fields = [
    variables.String("name"),
    variables.String("email")
]

# Create deduper
deduper = dedupe.Dedupe(fields)
deduper.prepare_training(data)

# Label at least 5 matches and 5 distincts
dedupe.console_label(deduper)

# Train the model
deduper.train()

# Use the correct threshold API
threshold = dedupe.static_threshold(deduper, data)

# Match
clusters = deduper.match(data, threshold)

# Print results
for cluster, score in clusters:
    print(f"Cluster: {cluster}, Score: {score}")
 