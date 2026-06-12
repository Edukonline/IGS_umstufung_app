#!/bin/bash
# Install CLI if missing: npm install -g @mermaid-js/mermaid-cli

# Create output directory if it doesn't exist
mkdir -p docs/diagrams/img

echo "Rendering Architecture Diagram..."
mmdc -i docs/diagrams/src/architecture.mmd -o docs/diagrams/img/architecture.png -b white -s 4

echo "Rendering Schema Diagram..."
mmdc -i docs/diagrams/src/schema.mmd -o docs/diagrams/img/schema.png -b white -s 4

echo "All diagrams rendered successfully!"
