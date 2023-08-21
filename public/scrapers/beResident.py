#!/usr/bin/env python3

from pyppeteer import launch
import asyncio


async def main():
    browser = await launch()
    page = await browser.newPage()
    await page.goto("https://www.beresident.com/")
    await page.screenshot({"path": "example.png"})
    await browser.close()


asyncio.get_event_loop().run_until_complete(main())
