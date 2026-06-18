#!/bin/bash
# Rendert die Mermaid-Diagramme als hochauflösende PNGs (weißer Hintergrund, 4x Scale).
# Voraussetzung (einmalig installieren): npm install -g @mermaid-js/mermaid-cli
set -e

mkdir -p docs/diagrams/img

echo "Rendering Architecture Diagram..."
mmdc -i docs/diagrams/src/architecture.mmd -o docs/diagrams/img/architecture.png -b white -s 4

echo "Rendering Schema (ERD) Diagram..."
mmdc -i docs/diagrams/src/schema.mmd -o docs/diagrams/img/schema.png -b white -s 4

echo "Rendering Authentication Flow Diagram..."
mmdc -i docs/diagrams/src/auth-flow.mmd -o docs/diagrams/img/auth-flow.png -b white -s 4

echo "All diagrams rendered successfully -> docs/diagrams/img/"
