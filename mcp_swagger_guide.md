# CivicPulse MCP and Swagger Integration Guide

This guide explains how to access the CivicPulse API interactively using **Swagger UI** and how to integrate it as tools in other projects or AI agents using the **Model Context Protocol (MCP)**.

---

## 1. Swagger UI (Interactive API Docs)

To view and interact with the API documentation in your browser:

1. Start your local Laravel development server:
   ```bash
   php artisan serve
   ```
2. Open your browser and navigate to:
   **[http://localhost:8000/api/v1/docs](http://localhost:8000/api/v1/docs)**
3. The OpenAPI 3.0 specification file is served dynamically at:
   **[http://localhost:8000/openapi.json](http://localhost:8000/openapi.json)**

*Note: You can use the **Authorize** button in Swagger UI to provide a Sanctum Bearer token for testing authenticated requests.*

---

## 2. Accessing the API via MCP in Other Projects

You can expose all CivicPulse endpoints as executable tools to LLMs/AI agents in other projects.

### Step 1: Install & Run the OpenAPI MCP Server

The official Model Context Protocol team provides an OpenAPI bridge server (`@modelcontextprotocol/server-openapi`) which dynamically converts any OpenAPI schema into MCP tools.

Run the server with `npx`, pointing it to your local CivicPulse OpenAPI JSON:

```bash
npx -y @modelcontextprotocol/server-openapi http://localhost:8000/openapi.json
```

Or read the file directly if the project is on the same machine:
```bash
npx -y @modelcontextprotocol/server-openapi /path/to/civic_pulse_backend/public/openapi.json
```

### Step 2: Configure Claude Desktop or MCP Client

To integrate these tools into Claude Desktop, add the following configuration to your `claude_desktop_config.json` (located at `~/.config/Claude/claude_desktop_config.json` on Linux/macOS or `%APPDATA%\Claude\claude_desktop_config.json` on Windows):

```json
{
  "mcpServers": {
    "civic-pulse": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-openapi",
        "http://localhost:8000/openapi.json"
      ]
    }
  }
}
```

### Step 3: Authenticating MCP Requests
The CivicPulse API endpoints require Sanctum Token authentication. To allow the MCP server to authenticate with your local API:

Set the `Authorization` header in the `args` of your MCP configuration or pass it as an environment variable:

```json
{
  "mcpServers": {
    "civic-pulse": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-openapi",
        "http://localhost:8000/openapi.json"
      ],
      "env": {
        "Authorization": "Bearer 1|laravel_sanctum_yourtoken..."
      }
    }
  }
}
```

*Tip: You can generate an API token for testing by using Laravel Tinker or running the test suite.*
