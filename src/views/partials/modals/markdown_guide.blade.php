<table class="markdown-guide">

	<tr>
		<td class="monospace">
			Main Heading<br />
			============
		</td>
		<td>
			<h1>Main Heading</h1>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			Sub Heading<br />
			-----------
		</td>
		<td>
			<h2>Sub Heading</h2>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			# Main Heading (Alternate)
		</td>
		<td>
			<h1>Main Heading</h1>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			## Sub Heading (Alternate)
		</td>
		<td>
			<h2>Sub Heading (Alternate)</h2>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			### Up to 6 Heading Levels...
		</td>
		<td>
			<h3>Up to 6 Heading Levels...</h3>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			>"**Taxation is theft**, purely and simply even though it is theft on a grand and colossal scale which no acknowledged criminals could hope to match. It is a *compulsory seizure* of the property of the State's inhabitants, or subjects."<br /><br />

			>-[Murray Rothbard](https://en.wikipedia.org...)
		</td>
		<td>
			<blockquote>
				"<strong>Taxation is theft</strong>, purely and simply even though it is theft on a grand and colossal scale which no acknowledged criminals could hope to match. It is a <em>compulsory seizure</em> of the property of the State's inhabitants, or subjects."<br /><br />
				-<a href="https://en.wikipedia.org/wiki/Murray_Rothbard" target="_blank">Murray Rothbard</a>
			</blockquote>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			- An unordered list<br />
			- You can also start with * or + instead of -<br />
			<pre class="plain">	- Add a *tab* character for sub list</pre>
		</td>
		<td>
			<ul>
				<li>An unordered list</li>
				<li>
					You can also start with * or + instead of -
					<ul>
						<li>Add a <em>tab</em> character for sub list</li>
					</ul>
				</li>
			</ul>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			1. An ordered list<br />
			1. The numbers you use don't matter<br />
			1. Making them all **1** helps with rearranging later
		</td>
		<td>
			<ol>
				<li>An ordered list</li>
				<li>The numbers you use don't matter</li>
				<li>Making them all <strong>1</strong> helps with rearranging later</li>
			</ol>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			<pre class="plain">	Indenting your lines with a **tab** character
	will place them in a "code" block.</pre>
		</td>
		<td>
			<pre><code>Indenting your lines with a <strong>tab</strong> character
will place them in a "code" block.</code></pre>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### Embed an image:<br /><br />

			![Image]({{ URL::to('...') }})
		</td>
		<td class="black">
			<h4>Embed an image:</h4>

			<img src="{{ Site::img('logo', 'regulus/fractal') }}" alt="Image Example" title="Image Example" />
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### Make an uploaded file URL:<br /><br />

			file:1
		</td>
		<td>
			<h4>Make an uploaded file URL:</h4>

			<p>{{ Site::uploadedFile('images/fractal-logo.png') }}</p>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### Make a link to an uploaded file:<br /><br />

			[file:1]
		</td>
		<td>
			<h4>Make a link to an uploaded file:</h4>

			<p><a href="#" target="_blank">Fractal Logo</a></p>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### Embed an image for an uploaded file:<br /><br />

			[image:1]
		</td>
		<td class="black">
			<h4>Embed an image for an uploaded file:</h4>

			<img src="{{ Site::img('logo', 'regulus/fractal') }}" alt="Image Example" title="Image Example" />
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### Make a content page URL:<br /><br />

			page:about
		</td>
		<td>
			<h4>Make a content page URL:</h4>

			<p>{{ URL::to('about') }}</p>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### Make a link to a content page:<br /><br />

			[page:about]
		</td>
		<td>
			<h4>Make a link to a content page:</h4>

			<p><a href="#">About Us</a></p>
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### Embed a media item:<br /><br />

			[media:1]
		</td>
		<td>
			<h4>Embed a media item:</h4>

			<img src="{{ Site::img('logo', 'regulus/fractal') }}" alt="Image Example" title="Image Example" />
		</td>
	</tr>

	<tr>
		<td class="monospace">
			#### The following also work for embedding content:<br /><br />

			[youtube:y0uTuB3-iDx]<br /><br />

			[vimeo:123456789]<br /><br />

			[soundcloud:123456789]<br /><br />
		</td>
		<td>
			<h2>No Preview Available</h2>
		</td>
	</tr>

</table>