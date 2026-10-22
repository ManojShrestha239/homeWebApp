# Node 22 on Alpine: LTS version, small image
FROM node:22-alpine

# Default folder for React commands
WORKDIR /app/frontend

# Document the Vite dev server port
EXPOSE 5173

# On start: install packages if the project exists, then run the dev server.
# If the project is missing (first run), wait instead of crashing.
CMD ["sh", "-c", "if [ -f package.json ]; then npm install && npm run dev -- --host 0.0.0.0 --port 5173; else echo 'Frontend not created yet. See Step 4.' && sleep infinity; fi"]