{if $MENU != ''}
	<ul class="sf-menu">
		{foreach from=$MENU item=item}
			<li {if $item.active}class="active"{/if}>
				<a href="{$item.link}" {if $item.new_window}target="_blank"{/if}>{$item.name}</a>
				{if $item.children}
					<ul>
						{foreach from=$item.children item=child}
							<li {if $child.active}class="active"{/if}>
								<a href="{$child.link}" {if $child.new_window}target="_blank"{/if}>{$child.name}</a>
								{if $child.children}
									<ul>
										{foreach from=$child.children item=sub}
											<li {if $sub.active}class="active"{/if}>
												<a href="{$sub.link}" {if $sub.new_window}target="_blank"{/if}>{$sub.name}</a>
											</li>
										{/foreach}
									</ul>
								{/if}
							</li>
						{/foreach}
					</ul>
				{/if}
			</li>
		{/foreach}
		{if $MENU_SEARCH}
			<li class="sf-search" style="float:right">
				<form id="searchbox" action="{$link->getPageLink('search')|escape:'html'}" method="get">
					<p>
						<input type="hidden" name="controller" value="search" />
						<input type="hidden" value="position" name="orderby"/>
						<input type="hidden" value="desc" name="orderway"/>
						<input type="text" name="search_query" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|escape:'html':'UTF-8'}{/if}" />
					</p>
				</form>
			</li>
		{/if}
	</ul>
{/if}